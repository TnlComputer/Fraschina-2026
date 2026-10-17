-- Clientes de distribución eliminados en el sistema anterior.
-- La baja del sistema viejo (bajaFCD.php) borraba físicamente al cliente pero dejaba sus pedidos
-- y su agenda. Para conservar ese historial se recrean como clientes dados de baja (status 'D').
SET foreign_key_checks = 0;

INSERT INTO
    fraschina_2024.distribucions (
        id,
        razonsocial,
        nomfantasia,
        info,
        status,
        created_at,
        updated_at
    )
SELECT
    h.cliente,
    CONCAT('Cliente eliminado (id ', h.cliente, ')'),
    CONCAT('Cliente eliminado (id ', h.cliente, ')'),
    'Recreado en la migración: el sistema anterior lo había borrado y conservaba sus pedidos.',
    'D',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
FROM (
        SELECT cliente FROM fraschin_backup.nropedidos
        UNION
        SELECT Idcliente FROM fraschin_backup.lineapedidos
        UNION
        SELECT idCliente FROM fraschin_backup.lineatareas
        UNION
        SELECT Idcliente FROM fraschin_backup.CDagenda
    ) h
WHERE
    h.cliente > 0
    AND h.cliente NOT IN (
        SELECT id
        FROM fraschina_2024.distribucions
    );

-- Cliente comodín para pedidos sin cliente (cliente 0 en el sistema anterior).
-- Id fijo y muy alto: el sistema anterior nunca llega a ese número, así no choca con clientes nuevos
-- y la actualización diaria (legado:sincronizar) lo reconoce siempre igual.
INSERT INTO
    fraschina_2024.distribucions (
        id,
        razonsocial,
        nomfantasia,
        info,
        status,
        created_at,
        updated_at
    )
VALUES (
        49999999,
        'Sin cliente (sistema anterior)',
        'Sin cliente (sistema anterior)',
        'Agrupa pedidos que en el sistema anterior no tenían cliente asignado.',
        'D',
        CURRENT_TIMESTAMP,
        CURRENT_TIMESTAMP
    );

SET foreign_key_checks = 1;
