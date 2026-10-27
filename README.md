# Fraschina — Sistema de gestión

Sistema web de gestión para **Fraschina S.R.L.**, empresa dedicada a la comercialización y
distribución de harinas y productos de molinería. Reemplaza al sistema anterior en PHP y a
las planillas Excel que se usaban para la operación diaria.

> **Software propietario.** Todos los derechos reservados — ver [LICENSE](LICENSE).
> Que el código sea visible no otorga permiso de uso, copia ni distribución.

---

## Qué hace el sistema

### Distribución
Venta y reparto a clientes propios (panaderías, comercios, industrias).
- **Clientes:** datos comerciales, dirección, horarios de entrega, días que reciben, forma de
  cobro, situación crediticia, productos y precios por cliente.
- **Agenda:** registro de llamados y contactos con cada cliente, con prioridad, acción,
  cotización y sus últimos pedidos a la vista.
- **Elegir cliente:** en pedidos, agenda y personal el cliente se busca escribiendo parte del
  nombre de fantasía, de la razón social o el número de cliente; la lista se filtra mientras se
  escribe y sin escribir nada se despliega completa.
- **Pedidos:** alta y modificación de pedidos con productos y tareas (cambios, retiros). Los
  totales se calculan con IVA (10,5 % o 21 % según el producto).
- **Reparto:** planilla del día ordenada por chofer y orden de entrega, con datos de factura
  (fecha, número, importe), aviso de factura impresa y calendario que resalta los días con
  pedidos. El importe de factura se marca en rojo cuando no coincide con el total del pedido
  (vacío no se marca). Al pasar el mouse se ven datos para facturar y entregar: productos,
  cantidad, precio unitario y total (sobre el importe), horario, días y observaciones de
  recepción (sobre el orden) y la ficha del cliente con CUIT, dirección y forma de cobro (sobre
  el nombre). *Ver pedido* muestra el pedido completo con los datos de facturación.
  Impresión de la planilla de reparto, de control y de recibos en PDF, y vista para el chofer
  en el celular. En el celular cada pedido se ve como una tarjeta con los mismos campos.

### Expedición — Central de Pedidos
Pedidos de clientes que se pasan a los molinos.
- **Pedidos** con número correlativo único y revisiones (`1234-0`, `1234-1`, …) cuando se
  modifican después de enviados. Pueden quedar *pendientes de confirmación* hasta que se
  confirman. Cada línea admite su propia calidad, precio, condición, planta, fecha (también
  tentativa), plazo y forma de pago. Incluye muestras sin cargo.
- **Configuración cliente-molino:** condiciones habituales que se precargan en cada pedido y
  qué incluye la planilla que se envía al molino.
- **Planilla de pedido en PDF**, archivada con nombre en código y enviada por mail a los
  destinatarios de cada molino.
- **Tablero**, **planificación semanal** tipo almanaque, **control de entregas** (transporte,
  horarios, kilos cargados y recibidos, remito, descarga y certificado) y **Diaria** (historial
  de cada movimiento).
- **Calidad (General):** parámetros de los certificados de calidad con filtros y exportación.
- **Informes:** tolvas y bolsas por molino, certificados pendientes de recibir.
- **Avisos** por mail (y WhatsApp, pendiente de proveedor) y **consultas automáticas** a los
  molinos por la mañana y por la tarde.

### Otros módulos
- **Molinos, Proveedores, Representación, Agro y Transporte:** fichas de empresas, personal de
  contacto y productos.
- **Agenda General:** contactos generales. En el celular los teléfonos se tocan para llamar y
  el mail para escribir.
- **Tools:** tablas auxiliares (calles, barrios, zonas, rubros, etc.), usuarios, roles y
  permisos, exportación de tablas a Excel.

### Uso en el celular
Todas las pantallas se adaptan al ancho del celular. Los listados se ven como tarjetas (una por
registro, cada dato con su rótulo) y ninguna tabla ensancha la página. Lo resuelven
`public/css/fraschina.css` y `public/js/fraschina.js` para todas las pantallas a la vez; una tabla
puede quedar afuera de las tarjetas con la clase `sin-tarjetas`, y un `<select>` largo se vuelve
buscable con el atributo `data-buscador`.

### Seguridad y trazabilidad
- **Permisos por sección:** cada usuario ve y opera solo los módulos que su rol habilita.
- **Nada se borra:** las bajas marcan el registro como eliminado y lo ocultan; la información
  queda en la base.
- **Auditoría:** cada alta, modificación y eliminación queda registrada con usuario, fecha,
  sector y valores anteriores y nuevos, además de ingresos, salidas e intentos fallidos. El
  administrador la consulta en *Tools → Auditoría* con filtros y exportación a Excel.

---

## Tecnología

- PHP 8.3+ · Laravel 13 · MySQL 8 / MariaDB 10.6
- AdminLTE (Bootstrap 4) · Vite · Tailwind · Tom Select (buscadores) · flatpickr (calendarios)
- spatie/laravel-permission (roles y permisos) · dompdf (PDF) · maatwebsite/excel (Excel)
- Interfaz y mensajes en español; zona horaria America/Argentina/Buenos_Aires

## Puesta en marcha (desarrollo)

```bash
composer install
npm install
cp .env.example .env        # completar base de datos, mail y variables EXPEDICION_*
php artisan key:generate
php artisan migrate
npm run dev                 # frontend (Vite)
php artisan serve           # backend en http://127.0.0.1:8000
```

Los usuarios iniciales se crean con `database/seeders/UsersTableSeeder.php`, que no se
versiona: copiar `UsersTableSeeder.php.example`, completarlo y definir `SEED_USER_PASSWORD`.

> **Windows:** si en el usuario existen variables de entorno `DB_HOST`, `DB_PASSWORD` o
> `DB_USER` (de otros proyectos), pisan al `.env`. Quitarlas de la sesión antes de usar
> `php artisan`.

### Tareas programadas
Respaldo diario en Excel de la Central de Pedidos y consultas automáticas a molinos. En el
servidor hace falta un cron cada minuto (con el PHP 8.3 del servidor):

```
* * * * * cd /ruta/al/sistema && php artisan schedule:run >> /dev/null 2>&1
```

Mientras convivan los dos sistemas, la actualización desde el sistema anterior corre en un
cron propio, de noche:

```
0 2 * * * cd /ruta/al/sistema && php artisan legado:sincronizar >> storage/logs/legado.log 2>&1
```

Trae solo lo que cambió desde la noche anterior; si un registro también se modificó en la app
nueva, no lo pisa y lo deja como duda de migración para revisar por pantalla. Con
`--simular` muestra qué haría sin cambiar nada.

### Migración de datos desde el sistema anterior
Los scripts de `conversion_tablas/` convierten la base del sistema anterior a la estructura
nueva sin perder registros (clientes dados de baja, pedidos sin cliente, líneas sueltas y
borradores se conservan como registros dados de baja). Se ejecutan en orden sobre una base
recién migrada.

### Tests
Los tests usan SQLite en memoria y nunca tocan la base real (requieren la extensión
`pdo_sqlite`):

```bash
php -d extension=pdo_sqlite vendor/bin/phpunit
```

### Seguridad de dependencias
Revisar periódicamente con `composer audit` y `npm audit`, y mantener Laravel dentro de una
versión con soporte de seguridad.

---

© 2023-2026 TnlComputer — Jorge Martínez. Todos los derechos reservados.
