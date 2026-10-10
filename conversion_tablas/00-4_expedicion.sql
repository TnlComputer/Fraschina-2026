-- Expedición: clientes, molinos y textos por cliente-molino del sistema anterior.
-- Las siglas vacías se completan con las primeras letras de la razón social (editables luego).
SET foreign_key_checks = 0;

INSERT INTO
    fraschina_2024.expedicion_molinos (
        id,
        nroMolino_id,
        calificacion,
        sigla,
        nroUPed,
        contMolino,
        estadoNroPedido,
        nroCliente_id,
        created_at,
        updated_at
    )
SELECT
    em.idExpMoli,
    NULLIF(em.nroMolinoExp, 0),
    IF(em.calificacionExp = '1', '1', '0'),
    COALESCE(
        NULLIF(TRIM(em.SiglaExp), ''),
        UPPER(LEFT(REPLACE(m.razonsocial, ' ', ''), 5)),
        CONCAT('M', em.idExpMoli)
    ),
    em.nroUPedExp,
    em.contMoliExp,
    em.estadoNroPed,
    NULLIF(em.nroClieEst, 0),
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
FROM fraschin_backup.expedicionmoli em
    LEFT JOIN fraschina_2024.molinos m ON m.id = em.nroMolinoExp;

INSERT INTO
    fraschina_2024.expedicion_clientes (
        id,
        representacion_id,
        sigla,
        linea1,
        linea2,
        linea3,
        linea4,
        linea5,
        linea6,
        obs,
        comentarios,
        created_at,
        updated_at
    )
SELECT
    ec.idExpClie,
    r.id,
    COALESCE(
        NULLIF(TRIM(ec.siglaExp), ''),
        UPPER(LEFT(REPLACE(r.razonsocial, ' ', ''), 5)),
        CONCAT('C', ec.idExpClie)
    ),
    ec.linea1Exp,
    ec.linea2Exp,
    ec.linea3Exp,
    ec.linea4Exp,
    ec.linea5Exp,
    ec.linea6Exp,
    ec.ObsExp,
    ec.ComentExp,
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
FROM fraschin_backup.expedicionclie ec
    LEFT JOIN fraschina_2024.representacions r ON r.id = ec.nroClieExp;

INSERT INTO
    fraschina_2024.expedicion_molinos_textos_clientes (
        id,
        expClie_id,
        expMolino_id,
        linea1,
        linea2,
        linea3,
        linea4,
        linea5,
        linea6,
        created_at,
        updated_at
    )
SELECT
    idExpMTC,
    idExpClie,
    idExpMoli,
    linea1Exp,
    linea2Exp,
    linea3Exp,
    linea4Exp,
    linea5Exp,
    linea6Exp,
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
FROM fraschin_backup.expedicionmtc
WHERE
    idExpClie IS NOT NULL
    AND idExpMoli IS NOT NULL;

SET foreign_key_checks = 1;
