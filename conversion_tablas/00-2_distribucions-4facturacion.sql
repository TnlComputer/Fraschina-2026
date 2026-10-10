-- Facturación, chofer, orden y totales de los pedidos de distribución.
-- En el sistema anterior estos datos no estaban en nropedidos sino repetidos en cada línea
-- (lineapedidos: fecFac_Ped, nroFac_Ped, impFac_Ped, chofer_Ped, orden_Ped, totalPedidoN).
-- totalPedidoN es el total del PEDIDO (con IVA), no de la línea: el total de cada línea se
-- recalcula con la regla de IVA de la app (10.5% si ivacda = 10.5, si no 21%) y el del pedido
-- es la suma de sus líneas. El total original queda en total_sistema_anterior.
-- Se puede volver a correr: solo actualiza.
SET @OLD_SQL_MODE = @@sql_mode;

SET SESSION sql_mode = '';

UPDATE fraschina_2024.distribucion_nropedidos p
    JOIN (
        SELECT
            CAST(nroPed AS UNSIGNED) nro,
            MAX(NULLIF(CAST(fecFac_Ped AS CHAR), '0000-00-00')) fecha_factura,
            MAX(NULLIF(NULLIF(TRIM(nroFac_Ped), ''), '0')) nro_factura,
            MAX(NULLIF(TRIM(impFac_Ped), '')) importe_factura,
            MAX(NULLIF(TRIM(chofer_Ped), '')) chofer,
            MAX(NULLIF(NULLIF(TRIM(orden_Ped), ''), '0')) orden,
            MAX(NULLIF(TRIM(totalPedidoN), '')) total_pedido,
            MAX(NULLIF(CAST(fecEntrega AS CHAR), '0000-00-00')) fecha_entrega
        FROM fraschin_backup.lineapedidos
        WHERE
            CAST(nroPed AS UNSIGNED) > 0
        GROUP BY
            CAST(nroPed AS UNSIGNED)
    ) l ON l.nro = p.id
SET
    p.fechaFactura = l.fecha_factura,
    p.nroFactura = LEFT(l.nro_factura, 20),
    p.totalFactura = CAST(REPLACE(l.importe_factura, ',', '.') AS DECIMAL(12, 2)),
    p.chofer = LEFT(l.chofer, 2),
    p.orden = CAST(l.orden AS UNSIGNED),
    p.total_sistema_anterior = CAST(REPLACE(l.total_pedido, ',', '.') AS DECIMAL(12, 2)),
    -- La fecha de entrega real es la de las líneas: el sistema anterior armaba el reparto con ella
    -- y en muchos pedidos dejaba vacía (o sin actualizar) la del pedido.
    p.fechaEntrega = IFNULL(l.fecha_entrega, p.fechaEntrega);

-- Total de cada línea con IVA incluido
UPDATE fraschina_2024.distribucion_linea_pedidos lp
    LEFT JOIN fraschina_2024.productos_c_d_a pr ON pr.id = lp.producto_id
SET
    lp.totalLinea = ROUND(
        IFNULL(lp.cantidad, 0) * IFNULL(lp.precio_unitario, 0) * IF(
            CAST(REPLACE(pr.ivacda, ',', '.') AS DECIMAL(5, 2)) = 10.5,
            1.105,
            1.21
        ),
        2
    );

-- Total del pedido = suma de sus líneas (el del sistema anterior queda en total_sistema_anterior:
-- no se actualizaba cuando se agregaban líneas después de crear el pedido)
UPDATE fraschina_2024.distribucion_nropedidos p
    LEFT JOIN (
        SELECT pedido_id, SUM(totalLinea) suma
        FROM fraschina_2024.distribucion_linea_pedidos
        WHERE deleted_at IS NULL
        GROUP BY pedido_id
    ) l ON l.pedido_id = p.id
SET
    p.totalPedido = IFNULL(l.suma, 0);

-- Líneas "marcadoras" de tareas: en el sistema anterior un pedido de tareas tenía una línea de
-- producto vacía con tareaEst_Ped = 1. No son productos: se ocultan (borrado lógico, quedan en la base).
UPDATE fraschina_2024.distribucion_linea_pedidos lp
    JOIN fraschin_backup.lineapedidos o ON o.id_linea = lp.id
SET
    lp.deleted_at = NOW()
WHERE
    o.tareaEst_Ped = 1
    AND (lp.producto_id IS NULL OR lp.producto_id = 0)
    AND lp.deleted_at IS NULL;

-- Tipo de pedido según sus líneas: P (productos), T (tareas), PT (ambos).
-- La conversión inicial dejaba 'D' y la impresión de Control filtra por P/PT.
UPDATE fraschina_2024.distribucion_nropedidos p
SET
    p.tipo = CASE
        WHEN EXISTS (SELECT 1 FROM fraschina_2024.distribucion_linea_pedidos lp WHERE lp.pedido_id = p.id AND lp.deleted_at IS NULL)
        AND EXISTS (SELECT 1 FROM fraschina_2024.distribucion_linea_tareas lt WHERE lt.pedido_id = p.id AND lt.deleted_at IS NULL) THEN 'PT'
        WHEN EXISTS (SELECT 1 FROM fraschina_2024.distribucion_linea_tareas lt WHERE lt.pedido_id = p.id AND lt.deleted_at IS NULL) THEN 'T'
        ELSE 'P'
    END;

-- Indicadores del cliente: el sistema usa 'SI' / 'NO'
UPDATE fraschina_2024.distribucions
SET
    lunes = CASE UPPER(TRIM(lunes)) WHEN 'ON' THEN 'SI' WHEN 'OFF' THEN 'NO' ELSE lunes END,
    sabado = CASE UPPER(TRIM(sabado)) WHEN 'ON' THEN 'SI' WHEN 'OFF' THEN 'NO' ELSE sabado END,
    auto = CASE UPPER(TRIM(auto)) WHEN 'ON' THEN 'SI' WHEN 'OFF' THEN 'NO' ELSE auto END,
    fac_imp = CASE UPPER(TRIM(fac_imp)) WHEN 'ON' THEN 'SI' WHEN 'OFF' THEN 'NO' ELSE fac_imp END;

SET SESSION sql_mode = @OLD_SQL_MODE;
