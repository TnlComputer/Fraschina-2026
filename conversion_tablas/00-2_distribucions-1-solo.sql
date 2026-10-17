SET foreign_key_checks = 0;

-- Distribucions
INSERT INTO
    fraschina_2024.distribucions (
        id,
        clisg_id,
        cliCDant_id,
        razonsocial,
        nomfantasia,
        dire_calle_id,
        dire_nro,
        dire_obs,
        codpost,
        barrio_id,
        zona_id,
        telefono,
        fax,
        cuit,
        fac_imp,
        municipio_id,
        marcas,
        info,
        contacto_id,
        horario,
        objetivos,
        comentarios,
        rubro_id,
        tamanio_id,
        modo_id,
        localidad_id,
        correo,
        piso,
        dpto,
        desde,
        hasta,
        lunes,
        sabado,
        productos_id,
        veraz_id,
        estado_id,
        obsrecep,
        desde1,
        hasta1,
        auto,
        productoCDA,
        cobro_id,
        tcobro_id,
        cobrar_id,
        status,
        created_at,
        updated_at
    )
SELECT
    Idcliente,
    IdcliSG,
    `Idcli-CDanterior`,
    RazonSocial,
    NomFantasia,
    dire_calle,
    dire_nro,
    dire_obs,
    codpos,
    Idbarrio,
    Zona,
    Telefono,
    Fax,
    CUIT,
    fac_imp,
    IdMunicipio,
    INFOenParticular,
    Info,
    Contacto,
    Horario,
    Objetivos,
    Comentarios,
    rubro,
    tamanio,
    modo,
    IdLocalidad,
    correo,
    piso,
    dpto,
    IF(IFNULL(TIME_TO_SEC(desde), 0) = 0, NULL, desde) AS desde, -- sin horario (vacío o 00:00) = NULL, igual en MySQL y MariaDB
    IF(IFNULL(TIME_TO_SEC(hasta), 0) = 0, NULL, hasta) AS hasta, -- sin horario (vacío o 00:00) = NULL, igual en MySQL y MariaDB
    lunes,
    sabado,
    productos,
    Idveraz,
    Idestado,
    obsRecep,
    IF(IFNULL(TIME_TO_SEC(desde1), 0) = 0, NULL, desde1) AS desde1, -- sin horario (vacío o 00:00) = NULL, igual en MySQL y MariaDB
    IF(IFNULL(TIME_TO_SEC(hasta1), 0) = 0, NULL, hasta1) AS hasta1, -- sin horario (vacío o 00:00) = NULL, igual en MySQL y MariaDB
    auto,
    productCDA,
    cobro,
    tcobro,
    cobrar,
    'A',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
FROM fraschin_backup.clientesdistribucion
WHERE
    Idcliente IS NOT NULL;

-- Distribucion Tareas
INSERT INTO
    fraschina_2024.distribucion_tareas (
        id,
        tarea,
        status,
        created_at,
        updated_at
    )
SELECT idTcdA, tareasCDA, 'A', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM fraschin_backup.tareascda
WHERE
    idTcdA IS NOT NULL;

-- Distribucion Personal
INSERT INTO
    fraschina_2024.distribucion_personal (
        id,
        nombre,
        apellido,
        distribucion_id,
        area_id,
        cargo_id,
        categoriacargo_id,
        teldirecto,
        interno,
        telcelular,
        profesion_id,
        telparticular,
        email,
        observaciones,
        fuera,
        status,
        created_at,
        updated_at
    )
SELECT
    Ipersona,
    Nombre,
    Apellido,
    Idcliente,
    Idarea,
    Idcargo,
    NULL,
    TelDirecto,
    LEFT(Interno, 4),
    TelCelular,
    Idprofesion,
    Telparticular,
    Mail,
    INFOparticular,
    CASE
        WHEN fuera = '1' THEN 1
        ELSE 0
    END,
    'A',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
FROM fraschin_backup.personalclientesdist
WHERE
    Ipersona IS NOT NULL;

    SET foreign_key_checks = 1;

