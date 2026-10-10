SET @OLD_SQL_MODE = @@sql_mode;

SET
    SESSION sql_mode =
REPLACE (
        @@sql_mode,
        'STRICT_TRANS_TABLES',
        ''
    );

SET SESSION sql_mode = REPLACE ( @@sql_mode, 'NO_ZERO_DATE', '' );

SET foreign_key_checks = 0;

-- No se descarta nada del sistema anterior:
-- * pedidos de clientes borrados -> clientes recreados en 00-2_distribucions-1b-eliminados.sql
-- * pedidos sin cliente -> cliente "Sin cliente (sistema anterior)"
-- * líneas cuyo pedido no existe -> pedido reconstruido con el número original
-- * líneas sin número de pedido (borradores nunca confirmados) -> un pedido dado de baja por cliente y fecha
SET @sin_cliente = (
        SELECT id
        FROM fraschina_2024.distribucions
        WHERE
            razonsocial = 'Sin cliente (sistema anterior)'
        LIMIT 1
    );

SET @obs_reconstruido = 'Pedido reconstruido en la migración: sus líneas existían pero el pedido no figuraba en el sistema anterior.';

SET @obs_borrador = 'Borrador sin confirmar en el sistema anterior: líneas cargadas sin número de pedido.';

-- Nro Pedidos
INSERT INTO
    fraschina_2024.distribucion_nropedidos (
        id,
        tipo,
        distribucion_id,
        fecha,
        fechaEntrega,
        observaciones,
        status,
        created_at,
        updated_at
    )
SELECT
    np.id_NroPed,
    'D',
    IF(d.id IS NULL, @sin_cliente, d.id),
    NULLIF(np.fechaPed, '0000-00-00'),
    NULLIF(np.fechaEnt, '0000-00-00'),
    np.obsPed,
    'A',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
FROM fraschin_backup.nropedidos np
    LEFT JOIN fraschina_2024.distribucions d ON d.id = np.cliente
ON DUPLICATE KEY UPDATE
    tipo = VALUES(tipo),
    distribucion_id = VALUES(distribucion_id),
    fecha = VALUES(fecha),
    fechaEntrega = VALUES(fechaEntrega),
    observaciones = VALUES(observaciones),
    status = VALUES(status),
    created_at = VALUES(created_at),
    updated_at = VALUES(updated_at);

-- Pedidos reconstruidos: líneas con número de pedido inexistente (conservan el número original)
INSERT INTO
    fraschina_2024.distribucion_nropedidos (
        id,
        tipo,
        distribucion_id,
        fecha,
        fechaEntrega,
        observaciones,
        status,
        created_at,
        updated_at
    )
SELECT
    l.nro,
    'D',
    IF(MIN(d.id) IS NULL, @sin_cliente, MIN(d.id)),
    MIN(l.fecha),
    MIN(l.entrega),
    @obs_reconstruido,
    'A',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
FROM (
        SELECT CAST(nroPed AS UNSIGNED) nro, idCliente cliente, NULLIF(fechaPed, '0000-00-00') fecha, NULLIF(fecEntrega, '0000-00-00') entrega
        FROM fraschin_backup.lineapedidos
        UNION ALL
        SELECT CAST(nroPed AS UNSIGNED), idCliente, NULLIF(fechaPed, '0000-00-00'), NULLIF(fecEntrega, '0000-00-00')
        FROM fraschin_backup.lineatareas
    ) l
    LEFT JOIN fraschina_2024.distribucions d ON d.id = l.cliente
WHERE
    l.nro > 0
    AND l.nro NOT IN (
        SELECT id
        FROM fraschina_2024.distribucion_nropedidos
    )
GROUP BY
    l.nro;

-- Borradores: líneas sin número de pedido, agrupadas por cliente y fecha en pedidos dados de baja
INSERT INTO
    fraschina_2024.distribucion_nropedidos (
        tipo,
        distribucion_id,
        fecha,
        fechaEntrega,
        observaciones,
        status,
        created_at,
        updated_at
    )
SELECT
    'D',
    b.cliente,
    b.fecha,
    MIN(b.entrega),
    @obs_borrador,
    'D',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
FROM (
        SELECT IF(d.id IS NULL, @sin_cliente, d.id) cliente, NULLIF(l.fechaPed, '0000-00-00') fecha, NULLIF(l.fecEntrega, '0000-00-00') entrega
        FROM (
                SELECT CONVERT(nroPed USING utf8mb4) nroPed, idCliente, fechaPed, fecEntrega FROM fraschin_backup.lineapedidos
                UNION ALL
                SELECT CONVERT(nroPed USING utf8mb4), idCliente, fechaPed, fecEntrega FROM fraschin_backup.lineatareas
            ) l
            LEFT JOIN fraschina_2024.distribucions d ON d.id = l.idCliente
        WHERE
            l.nroPed IS NULL
            OR TRIM(l.nroPed) IN ('', '0')
    ) b
GROUP BY
    b.cliente,
    b.fecha;

-- Linea Pedidos (todas: con pedido original, reconstruido o borrador)
INSERT INTO
    fraschina_2024.distribucion_linea_pedidos (
        id,
        pedido_id,
        distribucion_id,
        fecha,
        fechaEntrega,
        linea,
        producto_id,
        cantidad,
        precio_unitario,
        totalLinea,
        cambiar,
        retirar,
        estado_pedido,
        status,
        created_at,
        updated_at
    )
SELECT
    lp.id_linea,
    IF(
        lp.nroPed IS NULL OR TRIM(lp.nroPed) IN ('', '0'),
        b.id,
        CAST(lp.nroPed AS UNSIGNED)
    ),
    IF(d.id IS NULL, @sin_cliente, d.id),
    NULLIF(lp.fechaPed, '0000-00-00'),
    NULLIF(lp.fecEntrega, '0000-00-00'),
    lp.linea,
    lp.producto_id,
    CAST(
        NULLIF(TRIM(lp.cantidad), '') AS UNSIGNED
    ),
    CASE
        WHEN lp.preunit IS NOT NULL
        AND lp.preunit != '' THEN CAST(
            REPLACE (lp.preunit, ',', '.') AS DECIMAL(12, 2)
        )
        ELSE 0
    END,
    lp.totalPedidoN,
    lp.cambiar,
    lp.retirar,
    CAST(lp.estado_Ped AS CHAR(1)),
    1,
    NOW(),
    NOW()
FROM fraschin_backup.lineapedidos lp
    LEFT JOIN fraschina_2024.distribucions d ON d.id = lp.idCliente
    LEFT JOIN fraschina_2024.distribucion_nropedidos b ON b.observaciones = @obs_borrador COLLATE utf8mb4_unicode_ci
    AND b.distribucion_id = IF(d.id IS NULL, @sin_cliente, d.id)
    AND b.fecha <=> NULLIF(lp.fechaPed, '0000-00-00');

-- Linea Tareas (todas)
INSERT INTO
    fraschina_2024.distribucion_linea_tareas (
        id,
        pedido_id,
        distribucion_id,
        fecha,
        fechaEntrega,
        linea,
        tarea_id,
        detalles,
        estado_pedido,
        status,
        created_at,
        updated_at
    )
SELECT
    lt.id_linea,
    IF(
        lt.nroPed IS NULL OR TRIM(lt.nroPed) IN ('', '0'),
        b.id,
        CAST(lt.nroPed AS UNSIGNED)
    ),
    IF(d.id IS NULL, @sin_cliente, d.id),
    NULLIF(lt.fechaPed, '0000-00-00'),
    NULLIF(lt.fecEntrega, '0000-00-00'),
    lt.linea,
    lt.tarea_id,
    lt.detalles,
    CAST(
        lt.estado_Ped AS DECIMAL(1, 0)
    ),
    'A',
    NOW(),
    NOW()
FROM fraschin_backup.lineatareas lt
    LEFT JOIN fraschina_2024.distribucions d ON d.id = lt.idCliente
    LEFT JOIN fraschina_2024.distribucion_nropedidos b ON b.observaciones = @obs_borrador COLLATE utf8mb4_unicode_ci
    AND b.distribucion_id = IF(d.id IS NULL, @sin_cliente, d.id)
    AND b.fecha <=> NULLIF(lt.fechaPed, '0000-00-00');

SET foreign_key_checks = 1;
