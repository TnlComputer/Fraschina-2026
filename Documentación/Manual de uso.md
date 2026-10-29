# Manual de uso — Sistema Fraschina

Actualizado al 29/10/2026

Este manual explica cómo usar el sistema de Fraschina paso a paso. La **Parte 1** es para todos los usuarios. La **Parte 2** es para el Administrador y el Supervisor. La **Parte 3** explica la Auditoría, que ven el Administrador y el Supervisor.

## Introducción

El sistema reemplaza al programa anterior y a las planillas Excel de la operación diaria: clientes, pedidos, reparto, agenda y la Central de Pedidos a los molinos.

### Cómo entrar

1. Abrí el navegador (Chrome, Edge o el del celular) y entrá a **https://2026.fraschina.com.ar**.
2. Escribí tu correo y tu contraseña, y tocá **Acceder**.
3. Si te olvidaste la contraseña, pedile al Administrador que te la cambie.
4. Para salir, tocá tu nombre arriba a la derecha y elegí **Salir**.

### La pantalla de inicio

Al entrar ves un resumen del día según tu sector: pedidos del próximo reparto, pedidos sin factura cargada, pedidos sin chofer, contactos agendados para hoy, los últimos pedidos cargados y accesos rápidos.

![Pantalla de inicio](img/inicio.png)

- Cada recuadro de color tiene abajo un enlace que lleva a la pantalla correspondiente.
- **Accesos rápidos** abre directo *Nuevo pedido*, *Nuevo contacto en la agenda*, *Reparto de hoy* y *Clientes*.
- El ojo al final de cada pedido lo abre.

### El menú

A la izquierda está el menú con las secciones. **Cada usuario ve solo las secciones que habilita su rol**: si no ves una sección, pedile acceso al Administrador.

- Las secciones con flecha (Distribuciones, Expedición, Tools) se abren y muestran sus pantallas.
- El botón ☰ de arriba oculta o muestra el menú.
- El botón de pantalla completa (arriba a la derecha) agranda la página.

### Botones que se repiten en todas las pantallas

| Botón | Para qué sirve |
| --- | --- |
| Ojo (celeste) | Ver el registro completo |
| Lápiz (amarillo) | Modificar el registro |
| Tacho (rojo) | Eliminar el registro: el sistema pide confirmación |
| Nuevo | Dar de alta un registro |
| Buscador | Filtra mientras escribís, sin apretar Buscar |

**Nada se borra de verdad.** Al eliminar, el registro se oculta pero queda guardado. En Distribución podés verlos con los botones **Visibles / Eliminados / Todos**.

Si tu rol no tiene permiso para dar de alta, modificar o eliminar, el sistema muestra un mensaje como *«Su rol no tiene permiso para modificar registros»*.

### Buscar

- En los listados, escribí parte del nombre de fantasía, la razón social o el número: la lista se filtra sola al dejar de escribir.
- Los desplegables largos (clientes, molinos, calles, productos) también se pueden escribir para buscar.
- En los filtros con fechas y desplegables, el listado se actualiza apenas cambiás un valor.
- En los calendarios de fecha, los días con datos aparecen resaltados.

### En el celular

Todas las pantallas se adaptan al celular. Los listados se ven como **tarjetas**, una por registro, con cada dato y su rótulo. El menú se abre con el botón ☰. Los teléfonos se tocan para llamar y los correos para escribir.

<img src="img/menu-celular.png" alt="Menú en el celular" width="320">

# Parte 1 — Uso diario (todos los usuarios)

## Distribución

Distribución es la venta y el reparto a clientes propios: panaderías, comercios e industrias. Está en el menú **Distribuciones**, con cuatro pantallas: Distribución (clientes), Agenda, Reparto y Stock.

### Clientes

**Distribuciones → Distribución** muestra la lista de clientes con número, nombre de fantasía, razón social, dirección, barrio, teléfono y correo.

![Lista de clientes de Distribución](img/clientes.png)

1. Para encontrar un cliente, escribí en el buscador parte del nombre de fantasía, la razón social o el número.
2. Tocá el **ojo** para ver la ficha completa: datos comerciales, dirección, horarios y días que recibe, forma de cobro, situación crediticia, el **personal** de contacto y los **productos con su precio** para ese cliente.
3. Tocá el **lápiz** para modificar los datos.
4. Desde la ficha podés agregar contactos con **Nuevo Personal** y productos con **Nuevo Producto**.
5. **Nuevo cliente** da de alta un cliente. Mientras convivan los dos sistemas, los clientes nuevos se crean en el sistema anterior, porque su número es el código con el que se los llama.

Los botones **Visibles / Eliminados / Todos** muestran los clientes activos, los dados de baja o ambos.

### Cargar un pedido

![Nuevo pedido de Distribución](img/pedido-nuevo.png)

1. Tocá **Nuevo Pedido** (desde Distribución, Agenda, Reparto o el inicio).
2. En **Cliente**, escribí parte del nombre o el número y elegilo de la lista.
3. Elegí la **Fecha de Entrega**. La fecha del pedido se pone sola.
4. En **Agregar Producto o Tarea**, elegí el tipo (Producto o Tarea, por ejemplo un cambio o un retiro), el producto, el precio y la cantidad, y tocá **Agregar**. Repetí por cada producto.
5. Revisá la tabla **Detalles del Pedido**. Para quitar una línea, usá el botón de la columna Acción.
6. Si hace falta, escribí **Observaciones** y tocá **Guardar Pedido**.

Los totales incluyen IVA: 10,5 % o 21 % según el producto, igual que en el sistema anterior.

### Agenda

**Distribuciones → Agenda** registra los llamados y contactos con cada cliente.

![Agenda de Distribución](img/agenda.png)

1. Con **Todos / Hoy / Mañana** ves todos los contactos o solo los de ese día.
2. **Nuevo contacto** agenda un llamado: cliente, fecha, hora, prioridad, acción (llamada, mensaje, etc.) y los temas a hablar.
3. Mientras cargás el contacto ves los últimos pedidos del cliente.
4. **Nuevo pedido** carga el pedido directamente desde la agenda.
5. Los contactos con prioridad tienen una marca amarilla a la izquierda.

### Reparto

**Distribuciones → Reparto** es la planilla de entregas de un día, ordenada por chofer y orden de entrega. Arriba se ve la fecha remarcada y la cantidad de pedidos.

![Planilla de reparto de un día](img/reparto.png)

**Cambiar de día.** Elegí la fecha en el calendario (los días con pedidos aparecen resaltados) o usá **Día Anterior**, **Día Posterior** y **Hoy**.

**Completar la planilla.** En cada pedido cargá:

1. **Chofer**: la letra o el código del chofer.
2. **Orden**: el orden de entrega en el recorrido.
3. **Fecha Fac.**, **Nro. Fac.** e **Importe Fac.**: los datos de la factura.

Cada dato **se guarda solo al salir del campo** (o con Enter); no hay botón Guardar. A la derecha aparece un tilde verde cuando se guardó. Si no se pudo guardar, aparece un aviso rojo: revisá la conexión y volvé a cambiar el dato.

**Controles automáticos:**

- El **importe de factura se pone en rojo** si no coincide con el total del pedido. Al pasar el mouse muestra la diferencia.
- La **impresora amarilla** marca a los clientes que llevan la factura impresa.

**Datos al pasar el mouse:**

- Sobre el **nombre**: la ficha del cliente con CUIT, dirección, teléfono y forma de cobro.
- Sobre el **orden**: horario, días y observaciones de recepción.
- Sobre el **importe**: productos, cantidad, precio unitario y total.

![Ficha del cliente al pasar el mouse](img/reparto-ficha-cliente.png)

**Botones de cada pedido:** el primero (*Ver pedido*) muestra el pedido completo con los datos para facturar; el lápiz abre el pedido para modificarlo.

**Imprimir:**

- **Reparto** genera el PDF de la planilla de reparto.
- **Control** genera la planilla de control. Si falta algún dato de factura, avisa *«Faltan datos»* y nombra un pedido incompleto con los campos que le faltan. Podés **Cancelar** y completarlo, o **Continuar de todos modos**.

### Vista para el chofer

El botón **Chofer** abre el reparto pensado para el celular: una tarjeta por parada, en orden, con dirección, horario de recepción, productos, factura e importe a cobrar.

<img src="img/reparto-chofer.png" alt="Vista del chofer en el celular" width="320">

1. Arriba elegí la fecha y el chofer (o *Todos los choferes*).
2. **Mapa** abre la dirección en el mapa del celular.
3. **Llamar** llama al cliente.
4. **Detalle** muestra el pedido completo.
5. **Vista completa** vuelve a la planilla normal.

En el celular, la planilla normal de Reparto también se ve como tarjetas, con los mismos campos para cargar.

<img src="img/reparto-celular.png" alt="Planilla de reparto en el celular" width="320">

### Stock

**Distribuciones → Stock** todavía está en preparación y no tiene funciones.

## Expedición — Central de Pedidos

Expedición maneja los pedidos de clientes que se pasan a los molinos: se cargan, se confirman, se envían al molino por correo y se sigue cada entrega hasta recibir el certificado de calidad.

### Tablero

**Expedición → Tablero** muestra lo que hay que atender hoy: pedidos pendientes de confirmación, confirmados sin enviar al molino, entregas con fecha tentativa, certificados de calidad pendientes, y las entregas de hoy y de mañana.

![Tablero de la Central de Pedidos](img/expedicion-tablero.png)

### Cargar un pedido a molino

![Nuevo pedido de Expedición](img/expedicion-pedido-nuevo.png)

1. Tocá **Nuevo pedido** (en el Tablero o en Pedidos).
2. Elegí **Cliente** y **Molino** escribiendo para buscar. Se precargan las condiciones habituales de ese cliente con ese molino.
3. Revisá la **Fecha del pedido** y el **Tipo**: *Normal* o *Muestra sin cargo*.
4. Completá la **Línea 1**: calidad (obligatoria), tolvas, bolsas, kilos, precio con IVA, condición, planta, fecha de entrega, hora, plazo en días, forma de pago, lugar de entrega y observaciones.
5. Si la fecha todavía no es segura, tildá **Tentativa**. Esas entregas se ven en azul en la planificación. Podés cargar una **fecha alternativa** y una nota.
6. Para otra calidad u otra entrega, tocá **Agregar línea**. La ✕ roja quita una línea.
7. Terminá con uno de los dos botones:
    - **Guardar y confirmar**: el pedido recibe su número y queda listo para enviar.
    - **Guardar como pendiente de confirmación**: queda sin número hasta que el cliente lo confirme.

### Seguir un pedido

**Expedición → Pedidos** lista todos los pedidos. Filtrá por número, estado, molino, cliente o fechas; el listado se actualiza solo.

![Listado de pedidos de Expedición](img/expedicion-pedidos.png)

Al abrir un pedido, el recuadro **Acciones** ofrece:

| Acción | Cuándo aparece | Qué hace |
| --- | --- | --- |
| Confirmar y asignar número | Pedido pendiente | Le da el número correlativo |
| Descartar pedido pendiente | Pedido pendiente | Lo da de baja (pide confirmación) |
| Editar | Pedido no enviado | Modifica el pedido |
| Modificar (crea revisión) | Pedido ya enviado | Crea la revisión siguiente: 1234-0 pasa a 1234-1 |
| Ver Orden de Pedido | Pedido con número | Abre la planilla en PDF |
| Enviar / Reenviar al molino | Pedido con número | Manda la planilla por correo a los destinatarios del molino |
| Cambiar estado | Pedido confirmado | Confirmado, Pendiente de cierre, Cerrado o Anulado, con motivo opcional |

Los estados de un pedido son: **Pendiente de confirmación → Confirmado → Pendiente de cierre → Cerrado**. **Anulado** se puede elegir en cualquier momento. Para ver las revisiones reemplazadas, tildá **Revisiones viejas** en el listado.

### Planificación semanal

**Expedición → Planificación semanal** muestra las entregas de la semana como un almanaque. Las de fecha tentativa se ven en azul.

![Planificación semanal](img/expedicion-planificacion.png)

### Control de entregas

**Expedición → Control de entregas** muestra las entregas de hoy y de mañana. Con « y » pasás de día, o elegí una fecha y tocá **Ver**.

![Control de entregas](img/expedicion-control.png)

En cada entrega cargá transportista, chofer, carga, hora de carga, llegada, kilos del molino y kilos del cliente (la diferencia se calcula sola), remito, si descargó completo y si llegó el certificado.

### Diaria e Historial

- **Diaria** es el registro de cada movimiento de los pedidos: quién hizo qué y cuándo. Se filtra por fechas (Desde, Hasta), número de pedido y acción.
- **Historial (planillas)** muestra lo que se cargaba en las planillas Excel anteriores, solo para consulta.

![Diaria](img/expedicion-diaria.png)

### Calidad (General)

**Calidad (General)** guarda los parámetros de los certificados de calidad. Escribí en los recuadros debajo de cada título (fecha, molino, cliente, modo, producto o cualquier parámetro) y tocá Buscar. Nuevo carga un certificado y Exportar a Excel descarga lo filtrado.

![Calidad (General)](img/expedicion-calidad.png)

### Informes

- **Tolvas y bolsas**: totales por molino en un período. Incluye pedidos confirmados, pendientes de cierre y cerrados.
- **Certificados pendientes**: entregas descargadas cuyo certificado de calidad todavía no llegó.

### Configuración de Expedición

- **Clientes** y **Molinos**: los datos de cada uno y los textos al pie de la planilla de pedido.
- **Cliente-molino**: las condiciones habituales que se precargan en cada pedido y qué incluye la planilla que se manda al molino.
- **Avisos**: las direcciones de correo de cada molino que reciben las planillas y las consultas automáticas.

![Configuración cliente-molino](img/expedicion-cliente-molino.png)

### Fichas y Dudas de migración

- **Fichas** muestra las fichas que se llevaban en el Excel de Fichas, una por cliente. Elegí el cliente y tocá Filtrar.
- **Dudas de migración** lista los datos que no quedaron claros al pasar las planillas al sistema. Consultalas con el cliente y anotá la respuesta.

## Otros módulos

**Representación, Molino, Agro/AgroInd, Proveedor y Transporte** funcionan igual: cada uno es un listado de empresas con su ficha, su personal de contacto y, en algunos, sus productos.

![Listado de Molinos](img/molinos.png)

1. Buscá la empresa escribiendo en **Buscar por nombre**.
2. El **ojo** abre la ficha con dirección, teléfonos, el personal y los productos.
3. El **lápiz** modifica los datos y el **tacho** da de baja la empresa.
4. **Nuevo** da de alta una empresa. Desde la ficha agregás personal y productos.

## Agenda General

**Agenda General** guarda los contactos generales de la empresa (no los de clientes de Distribución). Se busca igual que los demás listados. En el celular, los teléfonos se tocan para llamar y el correo para escribir.

![Agenda General](img/agenda-general.png)

## Mi perfil

En **Perfil** (al final del menú) podés:

1. Cambiar tu **nombre** y tocar **Guardar nombre**.
2. Cambiar tu **correo electrónico** y tocar **Guardar correo**.
3. Cambiar tu **contraseña**: escribí la actual, la nueva dos veces y guardá. El ojo al lado de cada campo muestra lo que escribiste.

# Parte 2 — Administrador y Supervisor

El Administrador y el Supervisor manejan quién entra al sistema, qué puede hacer cada uno, las tablas auxiliares y los parámetros generales. Los roles con la sección Tools (por ejemplo gerencia) también ven Usuarios, Roles y las tablas auxiliares, pero no Configuración ni Auditoría. Todo está en el menú **Tools**.

## Qué puede hacer cada uno

| Tarea | Administrador | Supervisor |
| --- | --- | --- |
| Todas las secciones de uso diario | Sí | Sí |
| Alta, edición y baja de usuarios comunes | Sí | Sí |
| Modificar usuarios Administrador o Supervisor | Sí | No |
| Crear y modificar roles comunes | Sí | Sí |
| Modificar los roles admin y supervisor | Sí | No |
| Pantalla Permiso | Sí | No |
| Configuración | Sí | Sí |
| Auditoría | Sí | Sí |
| Tablas auxiliares y Exportar | Sí | Sí |

## Usuarios

**Tools → Usuarios → Usuario** lista los usuarios con su correo, su rol y su estado.

![Lista de usuarios](img/usuarios.png)

**Crear un usuario:**

1. Tocá **Nuevo**.
2. Completá **Nombre**, **Correo Electrónico**, **Contraseña** y **Confirmar Contraseña**.
3. Elegí el **Rol**: define todo lo que el usuario ve y puede hacer.
4. Dejá tildado **Activo** y tocá **Crear Usuario**.
5. Pasale al usuario su correo y su contraseña. Después la puede cambiar desde su Perfil.

**Modificar un usuario:** tocá el lápiz. Ahí podés cambiarle el rol o ponerle una contraseña nueva si se la olvidó.

**Desactivar un usuario:** tocá el botón rojo. El usuario ya no puede entrar, pero sus datos y su historial quedan. Se puede volver a activar. Nadie puede desactivar su propio usuario.

## Roles

Cada usuario tiene **un rol**. El rol define a qué secciones entra y si puede dar de alta, modificar o eliminar registros. **Tools → Usuarios → Rol** muestra cada rol con sus permisos y cuántos usuarios lo tienen.

![Roles y sus permisos](img/roles.png)

- **admin** (Super Usuario) tiene acceso total.
- **supervisor** entra a todas las secciones y además a **Configuración** y **Auditoría**.
- Los demás roles (gerencia, distribucion, administracion, expedicion, etc.) tienen solo las secciones tildadas.

**Crear o modificar un rol:**

![Formulario de rol](img/rol-formulario.png)

1. Tocá **Nuevo rol** o el lápiz de un rol.
2. Escribí el **Nombre del rol**.
3. En **Secciones** tildá las pantallas a las que entra: Representación, Distribución, Molino, Agro, Proveedores, Transportes, Expedición, Agenda General y Tools.
4. En **Acciones** tildá lo que puede hacer: **Alta registros**, **Editar registros**, **Eliminar registros**. Sin ninguna de las tres, el usuario solo consulta.
5. Tocá **Guardar**. El cambio vale para todos los usuarios con ese rol.

Un rol que tiene usuarios no se puede eliminar: primero asigná otro rol a esos usuarios. Los roles admin y supervisor solo los modifica el Administrador.

## Configuración

**Tools → Usuarios → Configuración** tiene los parámetros generales. Cada cambio queda en la Auditoría.

![Configuración](img/configuracion.png)

- **Correos → Modo de envío**: *No enviar* (solo se registran), *Enviar todo a una casilla de prueba* o *Enviar normalmente a los destinatarios*. Afecta las planillas y consultas a molinos y el «Olvidé mi contraseña».
- **Correo y nombre del remitente** que ven los molinos.
- **Consultas automáticas a molinos**: activarlas, la hora de la mañana (entregas del día), la de la tarde (cargas del día siguiente) y los días de la semana. Se envían a las direcciones de Expedición → Configuración → Avisos.
- **Pedidos de Expedición**: el número del primer pedido, el nombre de la planilla y cuántos días se guardan los respaldos diarios en Excel.

## Tablas auxiliares

**Tools → Ubicación** (barrios, calles, ciudades, localidades, zonas) y **Tools → Auxiliares** (acciones, áreas, cargos, estados, rubros, tamaños, prioridades, horas, etc.) son las listas que aparecen en los desplegables del sistema.

![Tabla auxiliar de calles](img/tabla-calles.png)

1. Buscá el valor escribiendo en el buscador.
2. **Nuevo** agrega un valor; el lápiz lo corrige.
3. Antes de agregar, buscá que no exista ya con otra escritura.

En cada tabla hay una fila sin nombre que se ve como **(sin dato)**. Es intencional: la usan los registros que no tienen ese dato. No la borres.

## Exportar tablas

**Tools → Auxiliares → Exportar Tablas** descarga cualquier tabla del sistema en Excel: elegí la tabla y tocá **Generar Exportación**.

![Exportar tablas](img/exportar.png)

# Parte 3 — Auditoría (Administrador y Supervisor)

La Auditoría registra todo lo que se hace en el sistema: quién, cuándo, en qué sector y qué valores cambió. La ven el Administrador y el Supervisor en **Tools → Usuarios → Auditoría**. Nadie puede modificarla ni borrarla.

![Auditoría con los cambios abiertos](img/auditoria.png)

## Qué se registra

| Acción | Cuándo aparece |
| --- | --- |
| Alta | Se crea un registro (cliente, pedido, contacto, usuario…) |
| Modificación | Se cambia un dato, con el valor anterior y el nuevo |
| Eliminación | Se da de baja un registro |
| Restauración | Se recupera un registro dado de baja |
| Ingreso / Salida | Un usuario entra o sale del sistema |
| Ingreso fallido | Alguien escribe mal la contraseña |
| Ingreso rechazado | Un usuario desactivado intenta entrar |

También quedan los procesos automáticos, como la actualización desde el sistema anterior. En esos casos el usuario figura como *Sistema (tarea programada)*.

## Cómo buscar

1. Arriba están los filtros: **Usuario**, **Sector**, **Acción**, **Desde**, **Hasta** y **Buscar** (registro, texto o valor).
2. El listado se actualiza solo al cambiar un filtro. **Limpiar** los borra todos.
3. En los calendarios de Desde y Hasta, los días con movimientos aparecen resaltados.

Ejemplos:

- *¿Quién cambió el precio de un cliente?* Sector Distribución, Acción Modificación y el nombre del cliente en Buscar.
- *¿Qué hizo un usuario ayer?* Elegí el usuario y la fecha de ayer en Desde y Hasta.
- *¿Alguien intentó entrar con una contraseña equivocada?* Acción Ingreso fallido.

## Cómo leer un movimiento

Cada fila muestra **Fecha y hora**, **Usuario**, **Sector**, **Acción** (con color) y **Registro**: una descripción y, debajo en gris, la tabla y el número del registro.

En las modificaciones, la columna **Cambios** dice cuántos campos cambiaron. Tocala y se abre una tabla con cada **Campo**, el valor de **Antes** (en rojo) y el de **Después** (en verde).

## Exportar

**Exportar a Excel** (arriba a la derecha) descarga lo que estás viendo con los filtros aplicados, hasta 50.000 movimientos.

En la pantalla de inicio, el Administrador y el Supervisor ven además los últimos movimientos de la auditoría.
