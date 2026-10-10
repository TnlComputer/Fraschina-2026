SET foreign_key_checks = 0;

INSERT INTO
    fraschina_2024.molinos (
        id, -- columna id de la tabla destino
        razonsocial,
        dire_calle,
        dire_nro,
        piso,
        dpto,
        dire_obs,
        codpost,
        localidad_id,
        telefono,
        municipio_id,
        fax,
        cuit,
        excenciones,
        iva_id,
        info,
        correo,
        marcas,
        zona,
        barrio_id,
        status,
        created_at,
        updated_at
    )
SELECT
    Idmolino AS id, -- Ajustar si el nombre de la columna en la tabla de origen es diferente
    razonsocial,
    dire_calle,
    dire_nro,
    piso,
    dpto,
    dire_obs,
    codpos AS codpost, -- Si el nombre de la columna de origen es diferente, ajusta aquí
    Idlocalidad AS localidad_id, -- Ajustar nombres si es necesario
    Telefono,
    IdMunicipio AS municipio_id, -- Ajustar nombres si es necesario
    Fax AS fax,
    CUIT AS cuit,
    Excenciones AS excenciones,
    Idiva AS iva_id, -- Ajustar nombres si es necesario
    Info AS info,
    correo,
    marcas,
    zona,
    Idbarrio AS barrio_id, -- Ajustar nombres si es necesario
    'A' AS status, -- Valor constante para el campo 'status'
    CURRENT_TIMESTAMP AS created_at, -- Fecha y hora actual para 'created_at'
    CURRENT_TIMESTAMP AS updated_at -- Fecha y hora actual para 'updated_at'
FROM fraschin_backup.molinos
WHERE
    Idmolino IS NOT NULL;

INSERT INTO
    fraschina_2024.molino_personal (
        id,
        nombre,
        apellido,
        molino_id,
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
    Ipersona AS id,
    Nombre AS nombre,
    Apellido AS apellido,
    Idmolino AS molino_id,
    Idarea AS area_id,
    Idcargo AS cargo_id,
    cate_cargo AS categoriacargo_id,
    TelDirecto AS teldirecto,
    LEFT(Interno, 4) AS interno, -- Limitar a 4 caracteres
    TelCelular AS telcelular,
    Idprofesion AS profesion_id,
    Telparticular AS telparticular,
    Mail AS email,
    INFOparticular AS observaciones,
    fuera,
    'A' AS status,
    CURRENT_TIMESTAMP AS created_at,
    CURRENT_TIMESTAMP AS updated_at
FROM fraschin_backup.personalmolino
WHERE
    Ipersona IS NOT NULL;

INSERT INTO
    proveedor_aux_productos (
        id, -- ID del producto
        nombre, -- Nombre del producto
        is_active, -- Estado activo (por defecto TRUE)
        created_at, -- Fecha y hora de creación
        updated_at -- Fecha y hora de última actualización
    )
SELECT
    Id_Producto AS id, -- ID del producto en la tabla original
    Producto AS nombre, -- Nombre del producto en la tabla original
    TRUE AS is_active, -- Asignar TRUE como valor constante para 'is_active'
    CURRENT_TIMESTAMP AS created_at, -- Fecha y hora actual para 'created_at'
    CURRENT_TIMESTAMP AS updated_at -- Fecha y hora actual para 'updated_at'
FROM fraschin_backup.auxproductosproveedores
WHERE
    Id_Producto IS NOT NULL;
-- Asegurarse de que 'Id_Producto' no sea nulo

INSERT INTO
    fraschina_2024.proveedores (
        id,
        razonsocial,
        dire_calle,
        dire_nro,
        dire_obs,
        codpost,
        localidad_id,
        telefono,
        fax,
        cuit,
        marcas,
        barrio_id,
        info,
        correo,
        municipio_id,
        rubro_id,
        familia,
        piso,
        dpto,
        status,
        created_at,
        updated_at
    )
SELECT
    Idproveedores AS id,
    RazonSocial AS razonsocial,
    dire_calle,
    dire_nro,
    dire_obs,
    codpos AS codpost,
    Idlocalidad AS localidad_id,
    Telefono AS telefono,
    Fax AS fax,
    CUIT AS cuit,
    marcas,
    Idbarrio AS barrio_id,
    Info AS info,
    correo,
    IdMunicipio AS municipio_id, -- asegúrate que exista en la tabla vieja
    IdRubro AS rubro_id, -- si no existe, pon NULL
    familia, -- si existe en la tabla vieja, si no NULL
    piso, -- idem
    dpto, -- idem
    'A' AS status,
    CURRENT_TIMESTAMP AS created_at,
    CURRENT_TIMESTAMP AS updated_at
FROM fraschin_backup.proveedores
WHERE
    Idproveedores IS NOT NULL;

UPDATE fraschin_backup.personalproveedores
SET
    Idprofesion = 132
WHERE
    Idprofesion = 40;

INSERT INTO
    fraschina_2024.proveedor_personal (
        id,
        nombre, -- Nombre del personal
        apellido, -- Apellido del personal
        proveedor_id, -- Relación con proveedor
        area_id, -- Relación con área
        cargo_id, -- Relación con cargo
        categoriacargo_id, -- Relación con categoría de cargo
        teldirecto, -- Teléfono directo
        interno, -- Teléfono interno
        telcelular, -- Teléfono celular
        profesion_id, -- Relación con profesión
        telparticular, -- Teléfono particular
        email, -- Correo electrónico
        observaciones, -- Observaciones (información adicional)
        fuera, -- Indicador de fuera
        status, -- Estado del registro
        created_at, -- Fecha de creación
        updated_at -- Fecha de actualización
    )
SELECT
    Ipersona AS id,
    Nombre AS nombre, -- Nombre
    Apellido AS apellido, -- Apellido
    Idproveedores AS proveedor_id, -- Relación con proveedores
    Idarea AS area_id, -- Relación con área
    Idcargo AS cargo_id, -- Relación con cargo
    cate_cargo AS categoriacargo_id, -- Relación con categoría de cargo
    TelDirecto AS teldirecto, -- Teléfono directo
    Interno AS interno, -- Teléfono interno
    TelCelular AS telcelular, -- Teléfono celular
    Idprofesion AS profesion_id, -- Relación con profesión
    Telparticular AS telparticular, -- Teléfono particular
    Mail AS email, -- Correo electrónico
    INFOparticular AS observaciones, -- Observaciones
    fuera, -- Indicador de fuera
    'A' AS status, -- Estado activo por defecto
    CURRENT_TIMESTAMP AS created_at, -- Fecha actual como created_at
    CURRENT_TIMESTAMP AS updated_at -- Fecha actual como updated_at
FROM fraschin_backup.personalproveedores
WHERE
    Ipersona IS NOT NULL;

INSERT INTO
    fraschina_2024.proveedor_productos (
        id, -- ID principal
        proveedor_id, -- Relación con proveedores
        producto_id, -- Relación con productos
        status, -- Estado del registro
        created_at, -- Fecha de creación
        updated_at -- Fecha de actualización
    )
SELECT
    idProProveedor AS id, -- Mapeo de ID
    Idproveedores AS proveedor_id, -- Relación con proveedores
    Idproducto AS producto_id, -- Relación con productos
    'A' AS status, -- Estado activo por defecto
    CURRENT_TIMESTAMP AS created_at, -- Fecha actual como created_at
    CURRENT_TIMESTAMP AS updated_at -- Fecha actual como updated_at
FROM fraschin_backup.productoporproveedor
WHERE
    idProProveedor IS NOT NULL;
--

INSERT INTO
    representacion_aux_productos (
        id, -- ID del producto
        nombre, -- Nombre del producto
        is_active, -- Estado activo (por defecto TRUE)
        created_at, -- Fecha y hora de creación
        updated_at -- Fecha y hora de última actualización
    )
SELECT
    Id_Producto AS id, -- ID del producto en la tabla original
    Producto AS nombre, -- Nombre del producto en la tabla original
    TRUE AS is_active, -- Asignar TRUE como valor constante para 'is_active'
    CURRENT_TIMESTAMP AS created_at, -- Fecha y hora actual para 'created_at'
    CURRENT_TIMESTAMP AS updated_at -- Fecha y hora actual para 'updated_at'
FROM fraschin_backup.auxproductos
WHERE
    Id_Producto IS NOT NULL;
-- Asegurarse de que 'Id_Producto' no sea nulo

UPDATE fraschin_backup.personalclientes
SET
    `Idprofesion` = 132
WHERE
    `Idprofesion` = 40;

INSERT INTO
    fraschina_2024.representacion_personal (
        `id`,
        `nombre`,
        `apellido`,
        `representacion_id`,
        `area_id`,
        `cargo_id`,
        `categoriacargo_id`,
        `teldirecto`,
        `interno`,
        `telcelular`,
        `profesion_id`,
        `telparticular`,
        `email`,
        `observaciones`,
        `fuera`,
        `status`,
        `created_at`,
        `updated_at`
    )
SELECT
    `Ipersona` AS `id`,
    `Nombre`,
    `Apellido`,
    IF(
        `Idcliente` = 0,
        NULL,
        `Idcliente`
    ) AS `representacion_id`, -- Asigna NULL si es 0
    NULLIF(`Idarea`, '') AS `area_id`, -- vacíos del sistema viejo pasan a NULL
    NULLIF(`Idcargo`, '') AS `cargo_id`,
    NULLIF(`cate_cargo`, '') AS `categoriacargo_id`,
    `TelDirecto` AS `teldirecto`,
    `Interno` AS `interno`,
    `TelCelular` AS `telcelular`,
    NULLIF(`Idprofesion`, '') AS `profesion_id`,
    `Telparticular` AS `telparticular`,
    `Mail` AS `email`,
    `INFOparticular` AS `observaciones`,
    IFNULL(NULLIF(`fuera`, ''), 0) AS `fuera`,
    'A' AS `status`,
    CURRENT_TIMESTAMP AS `created_at`,
    CURRENT_TIMESTAMP AS `updated_at`
FROM fraschin_backup.personalclientes
WHERE
    Ipersona IS NOT NULL;

SET foreign_key_checks = 1;