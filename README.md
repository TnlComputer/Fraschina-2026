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
- **Pedidos:** alta y modificación de pedidos con productos y tareas (cambios, retiros). Los
  totales se calculan con IVA (10,5 % o 21 % según el producto).
- **Reparto:** planilla del día ordenada por chofer y orden de entrega, con datos de factura
  (fecha, número, importe), aviso de factura impresa y calendario que resalta los días con
  pedidos. Impresión de la planilla de reparto, de control y de recibos en PDF.

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
- **Agenda General:** contactos generales.
- **Tools:** tablas auxiliares (calles, barrios, zonas, rubros, etc.), usuarios, roles y
  permisos, exportación de tablas a Excel.

### Seguridad y trazabilidad
- **Permisos por sección:** cada usuario ve y opera solo los módulos que su rol habilita.
- **Nada se borra:** las bajas marcan el registro como eliminado y lo ocultan; la información
  queda en la base.
- **Auditoría:** cada alta, modificación y eliminación queda registrada con usuario, fecha,
  sector y valores anteriores y nuevos, además de ingresos, salidas e intentos fallidos. El
  administrador la consulta en *Tools → Auditoría* con filtros y exportación a Excel.

---

## Tecnología

- PHP 8.2+ · Laravel 11 · MySQL 8
- AdminLTE (Bootstrap 4) · Vite · Tailwind
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
servidor hace falta un cron cada minuto:

```
* * * * * cd /ruta/al/sistema && php artisan schedule:run >> /dev/null 2>&1
```

### Migración de datos desde el sistema anterior
Los scripts de `conversion_tablas/` convierten la base del sistema anterior a la estructura
nueva sin perder registros (clientes dados de baja, pedidos sin cliente, líneas sueltas y
borradores se conservan como registros dados de baja). Se ejecutan en orden sobre una base
recién migrada.

### Tests
Los tests usan SQLite en memoria y nunca tocan la base real:

```bash
php vendor/phpunit/phpunit/phpunit
```

---

© 2023-2026 TnlComputer — Jorge Martínez. Todos los derechos reservados.
