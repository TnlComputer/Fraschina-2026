SET foreign_key_checks = 0;

INSERT INTO
    fraschina_2024.transportes (
        id, -- ID principal
        razonsocial, -- Razón social
        dire_calle, -- Calle
        dire_nro, -- Número
        piso, -- Piso
        dpto, -- Departamento
        dire_obs, -- Observaciones de la dirección
        codpost, -- Código postal
        localidad_id, -- Relación con localidad
        telefono, -- Teléfono
        fax, -- Fax
        cuit, -- CUIT
        excenciones, -- Exenciones
        barrio_id, -- Relación con barrio
        info, -- Información adicional
        correo, -- Correo electrónico
        municipio_id, -- Relación con municipio
        marcas, -- Marcas
        status, -- Estado del registro
        created_at, -- Fecha de creación
        updated_at -- Fecha de actualización
    )
SELECT
    Idtransporte AS id, -- ID principal
    RazonSocial AS razonsocial, -- Razón social
    dire_calle, -- Calle
    dire_nro, -- Número
    piso, -- Piso
    dpto, -- Departamento
    dire_obs, -- Observaciones de la dirección
    codpos AS codpost, -- Código postal
    Idlocalidad AS localidad_id, -- Relación con localidad
    Telefono AS telefono, -- Teléfono
    Fax AS fax, -- Fax
    CUIT AS cuit, -- CUIT
    Excenciones AS excenciones, -- Exenciones
    Idbarrio AS barrio_id, -- Relación con barrio
    Info AS info, -- Información adicional
    correo, -- Correo electrónico
    IdMunicipio AS municipio_id, -- Relación con municipio
    marcas, -- Marcas
    'A' AS status, -- Estado activo por defecto
    CURRENT_TIMESTAMP AS created_at, -- Fecha actual como created_at
    CURRENT_TIMESTAMP AS updated_at -- Fecha actual como updated_at
FROM fraschin_backup.transportes
WHERE
    Idtransporte IS NOT NULL;
-- Asegurar que el ID no sea nulo

INSERT INTO
    fraschina_2024.transporte_personal (
        id, -- ID principal
        nombre, -- Nombre del personal
        apellido, -- Apellido del personal
        transporte_id, -- Relación con transporte
        area_id, -- Relación con área
        cargo_id, -- Relación con cargo
        categoriacargo_id, -- Relación con categoría de cargo
        teldirecto, -- Teléfono directo
        interno, -- Extensión interna
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
    Ipersona AS id, -- ID principal
    Nombre AS nombre, -- Nombre
    Apellido AS apellido, -- Apellido
    Idtransporte AS transporte_id, -- Relación con transporte
    Idarea AS area_id, -- Relación con área
    Idcargo AS cargo_id, -- Relación con cargo
    cate_cargo AS categoriacargo_id, -- Relación con categoría de cargo
    TelDirecto AS teldirecto, -- Teléfono directo
    Interno AS interno, -- Extensión interna
    TelCelular AS telcelular, -- Teléfono celular
    Idprofesion AS profesion_id, -- Relación con profesión
    Telparticular AS telparticular, -- Teléfono particular
    Mail AS email, -- Correo electrónico
    INFOparticular AS observaciones, -- Observaciones
    IF(fuera = '1', 1, 0) AS fuera, -- Convertir fuera de varchar a tinyint
    'A' AS status, -- Estado activo por defecto
    CURRENT_TIMESTAMP AS created_at, -- Fecha actual como created_at
    CURRENT_TIMESTAMP AS updated_at -- Fecha actual como updated_at
FROM fraschin_backup.personaltransportes
WHERE
    Ipersona IS NOT NULL;
-- Asegurar que el ID no sea nulo
-- Asegurar que el ID no sea nulo

SET foreign_key_checks = 1;

INSERT INTO
    fraschina_2024.representacions (
        id, -- Respetamos el ID antiguo
        razonsocial,
        dire_calle,
        dire_nro,
        piso,
        codpost,
        dire_obs,
        telefono,
        fax,
        cuit,
        excenciones,
        iva_id,
        marcas,
        info,
        contacto,
        horario,
        objetivos,
        comentarios,
        fechagira,
        correo,
        dpto,
        barrio_id,
        localidad_id,
        zona_id,
        municipio_id
    )
SELECT
    idcliente AS id, -- Tomamos el ID de la tabla vieja
    RazonSocial,
    dire_calle,
    dire_nro,
    piso,
    codpos,
    dire_obs,
    Telefono,
    Fax,
    CUIT,
    Excenciones,
    Idiva,
    NULLIF(TRIM(INFOenParticular), "") AS marcas, -- "Marca/s" en el sistema anterior (vacío = NULL, como en la app)
    Info,
    Contacto,
    Horario,
    Objetivos,
    Comentarios,
    FechaGira,
    correo,
    dpto,
    Idbarrio AS barrio_id,
    Idlocalidad AS localidad_id,
    Zona AS zona_id,
    IdMunicipio AS municipio_id
FROM fraschin_backup.clientes;

INSERT IGNORE INTO
    fraschina_2024.distribucion_agenda (
        id,
        fecha,
        hs,
        prioridad_id,
        accion_id,
        temas,
        cotizacion,
        fecCotEnt,
        fecCot,
        producto_id,
        distribucion_id,
        persona_id,
        tipoper_id,
        veraz_id,
        estado_id,
        contacto_id,
        cargo_id,
        barrio_id,
        municipio_id,
        localidad_id,
        zona_id,
        rubro_id,
        tamanio_id,
        modo_id,
        pedido_id,
        estadoPedido,
        status,
        created_at,
        updated_at
    )
-- Referencias inexistentes (ej. producto 0, persona borrada) pasan a NULL para no perder la agenda.
SELECT
    a.id_CDAg AS id,
    a.fecha_CDAg AS fecha,
    a.hora_CDAg AS hs,
    r0.id AS prioridad_id,
    r1.id AS accion_id,
    a.temas_CDAg AS temas,
    a.cotiz_CDAg AS cotizacion,
    a.fecCotEnt_CDAg AS fecCotEnt,
    a.fecCot_CDAg AS fecCot,
    r2.id AS producto_id,
    a.Idcliente AS distribucion_id,
    r3.id AS persona_id,
    r4.id AS tipoper_id,
    r5.id AS veraz_id,
    r6.id AS estado_id,
    r7.id AS contacto_id,
    r8.id AS cargo_id,
    r9.id AS barrio_id,
    r10.id AS municipio_id,
    r11.id AS localidad_id,
    r12.id AS zona_id,
    r13.id AS rubro_id,
    r14.id AS tamanio_id,
    r15.id AS modo_id,
    r16.id AS pedido_id,
    a.estadoPed AS estadoPedido,
    'A' AS status,
    CURRENT_TIMESTAMP AS created_at,
    CURRENT_TIMESTAMP AS updated_at
FROM fraschin_backup.CDagenda a
    LEFT JOIN fraschina_2024.auxprioridades r0 ON r0.id = a.idPrio
    LEFT JOIN fraschina_2024.auxacciones r1 ON r1.id = a.idAccion
    LEFT JOIN fraschina_2024.productos_c_d_a r2 ON r2.id = a.idProductoCDA
    LEFT JOIN fraschina_2024.distribucion_personal r3 ON r3.id = a.id_persCD
    LEFT JOIN fraschina_2024.auxtipopersonal r4 ON r4.id = a.id_tipoper
    LEFT JOIN fraschina_2024.auxveraz r5 ON r5.id = a.idVeraz
    LEFT JOIN fraschina_2024.auxestados r6 ON r6.id = a.idEstado
    LEFT JOIN fraschina_2024.auxcontacto r7 ON r7.id = a.idContInit
    LEFT JOIN fraschina_2024.auxcargos r8 ON r8.id = a.idCargo
    LEFT JOIN fraschina_2024.auxbarrios r9 ON r9.id = a.idBarrio
    LEFT JOIN fraschina_2024.auxmunicipios r10 ON r10.id = a.idCiudad
    LEFT JOIN fraschina_2024.auxlocalidades r11 ON r11.id = a.idLocalidad
    LEFT JOIN fraschina_2024.auxzonas r12 ON r12.id = a.idZona
    LEFT JOIN fraschina_2024.auxrubros r13 ON r13.id = a.idRubro
    LEFT JOIN fraschina_2024.auxtamanio r14 ON r14.id = a.idTamano
    LEFT JOIN fraschina_2024.auxmodos r15 ON r15.id = a.idModo
    LEFT JOIN fraschina_2024.distribucion_nropedidos r16 ON r16.id = a.pedidosID
WHERE
    a.Idcliente IN (SELECT id FROM fraschina_2024.distribucions);

SET foreign_key_checks = 1;