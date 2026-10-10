<?php

use App\Http\Controllers\AccionController;
use App\Http\Controllers\AgendaGralController;
use App\Http\Controllers\AgroController;
use App\Http\Controllers\AgroPersonalController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\BarrioController;
use App\Http\Controllers\CalleController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\Contacto_inicialController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DistribucionAgendaController;
use App\Http\Controllers\DistribucionController;
use App\Http\Controllers\DistribucionPedidoController;
use App\Http\Controllers\DistribucionPersonalController;
use App\Http\Controllers\DistribucionProductoController;
use App\Http\Controllers\DistribucionRepartoController;
use App\Http\Controllers\DistribucionStockController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\ExpedicionClienteController;
use App\Http\Controllers\ExpedicionControlController;
use App\Http\Controllers\ExpedicionController;
use App\Http\Controllers\ExpedicionDiariaController;
use App\Http\Controllers\ExpedicionGeneralController;
use App\Http\Controllers\ExpedicionInformeController;
use App\Http\Controllers\ExpedicionMolinoController;
use App\Http\Controllers\ExpedicionMolinoTextoClienteController;
use App\Http\Controllers\ExpedicionNotificacionController;
use App\Http\Controllers\ExpedicionPedidoController;
use App\Http\Controllers\ExpedicionPlanificacionController;
use App\Http\Controllers\ExportarController;
use App\Http\Controllers\FamiliaController;
use App\Http\Controllers\FichasController;
use App\Http\Controllers\HoraController;
use App\Http\Controllers\LocalidadController;
use App\Http\Controllers\ModoController;
use App\Http\Controllers\MolinoController;
use App\Http\Controllers\MolinoPersonalController;
use App\Http\Controllers\MunicipioController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PrioridadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ProveedorPersonalController;
use App\Http\Controllers\ProveedorProductoController;
use App\Http\Controllers\RepresentacionController;
use App\Http\Controllers\RepresentacionPersonalController;
use App\Http\Controllers\RepresentacionProductoController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RubroController;
use App\Http\Controllers\TamanioController;
use App\Http\Controllers\TareaController;
use App\Http\Controllers\Tipo_PersonaController;
use App\Http\Controllers\TransporteController;
use App\Http\Controllers\TransportePersonalController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VerazController;
use App\Http\Controllers\ZonaController;
use Illuminate\Support\Facades\Route;

// La raíz lleva al inicio (o al ingreso si no hay sesión)
Route::get('/', fn() => redirect()->route(auth()->check() ? 'dashboard' : 'login'));

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

// PROFILE
Route::middleware('auth')->group(function () {
  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::patch('/profile/email', [ProfileController::class, 'updateEmail'])->name('profile.updateEmail');
  Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
});

// Cada sección exige el mismo permiso que usa el menú (config/adminlte.php).
// permiso_99 (Super Usuario) pasa siempre por el Gate::before de AppServiceProvider.

// REPRESENTACION (permiso_1)
Route::middleware(['auth', 'permission:permiso_1'])->group(function () {
  Route::resource('/representacion', RepresentacionController::class);

  Route::get('/representacion/{representacion}/representacion_personal/create', [RepresentacionPersonalController::class, 'create'])
    ->name('representacion_personal.create');
  Route::resource('/representacion/representacion_personal', RepresentacionPersonalController::class)
    ->except(['create']);

  Route::get('/representacion/{representacion}/representacion_producto/create', [RepresentacionProductoController::class, 'create'])
    ->name('representacion_producto.create');
  Route::resource('/representacion/representacion_producto', RepresentacionProductoController::class)
    ->except(['create']);
});

// DISTRIBUCION (permiso_2)
Route::middleware(['auth', 'permission:permiso_2'])->group(function () {
  Route::resource('/distribucion', DistribucionController::class);

  Route::get('/distribucion/{distribucion}/distribucion_personal/create', [DistribucionPersonalController::class, 'create'])
    ->name('distribucion_personal.create');
  Route::resource('/distribucion/distribucion_personal', DistribucionPersonalController::class)
    ->except(['create']);

  Route::get('/distribucion/{distribucion}/distribucion_producto/create', [DistribucionProductoController::class, 'create'])
    ->name('distribucion_producto.create');
  Route::resource('/distribucion/distribucion_producto', DistribucionProductoController::class)
    ->except(['create']);

  // Agenda
  Route::get('distribucion_agenda/create', [DistribucionAgendaController::class, 'create'])
    ->name('distribucion_agenda.create');
  Route::resource('distribucion_agenda', DistribucionAgendaController::class)->except(['create']);
  Route::get('distribucion_agenda/{id}/personal', [DistribucionAgendaController::class, 'getPersonal'])->name('distribucion.getPersonal');

  // Pedidos
  Route::resource('distribucion_pedido', DistribucionPedidoController::class)
    ->only(['create', 'store', 'edit', 'update']);
  Route::get('/productos-y-tareas-por-cliente/{clienteId}', [DistribucionPedidoController::class, 'getProductosYTareasPorCliente']);
  Route::get('/distribucion/{id}/productos-tareas', [DistribucionPedidoController::class, 'getProductosTareas']);

  // Reparto
  Route::get('/distribucion/reparto/fechas', [DistribucionRepartoController::class, 'fechasConPedidos'])
    ->name('distribucion_reparto.fechas');
  Route::get('/distribucion/reparto/imprimir', [DistribucionRepartoController::class, 'imprimirReparto'])
    ->name('distribucion_reparto.imprimirReparto');
  Route::get('/distribucion/reparto/control', [DistribucionRepartoController::class, 'imprimirControl'])
    ->name('distribucion_reparto.imprimirControl');
  Route::get('/distribucion/pedidos/{id}/imprimirRecibo', [DistribucionRepartoController::class, 'imprimirRecibo'])
    ->name('distribucion_reparto.imprimirRecibo');
  Route::get('/convertir-letras/{numero}/{moneda}', [DistribucionRepartoController::class, 'convertirNumeroALetras']);
  Route::resource('distribucion_reparto', DistribucionRepartoController::class)
    ->only(['index', 'show', 'update']);

  // Stock
  Route::resource('distribucion_stock', DistribucionStockController::class);
});

// MOLINO (permiso_3)
Route::middleware(['auth', 'permission:permiso_3'])->group(function () {
  Route::resource('/molino', MolinoController::class);

  Route::get('/molino/{molino}/molino_personal/create', [MolinoPersonalController::class, 'create'])
    ->name('molino_personal.create');
  Route::resource('/molino/molino_personal', MolinoPersonalController::class)
    ->except(['create']);
});

// AGRO (permiso_4)
Route::middleware(['auth', 'permission:permiso_4'])->group(function () {
  Route::resource('/agro', AgroController::class);

  Route::get('/agro/{agro}/agro_personal/create', [AgroPersonalController::class, 'create'])
    ->name('agro_personal.create');
  Route::resource('/agro/agro_personal', AgroPersonalController::class)
    ->except(['create']);
});

// PROVEEDOR (permiso_5)
Route::middleware(['auth', 'permission:permiso_5'])->group(function () {
  Route::resource('/proveedor', ProveedorController::class);

  Route::get('/proveedor/{proveedor}/proveedor_personal/create', [ProveedorPersonalController::class, 'create'])
    ->name('proveedor_personal.create');
  Route::resource('/proveedor/proveedor_personal', ProveedorPersonalController::class)
    ->except(['create']);

  Route::get('/proveedor/{proveedor}/proveedor_producto/create', [ProveedorProductoController::class, 'create'])
    ->name('proveedor_producto.create');
  Route::resource('/proveedor/proveedor_producto', ProveedorProductoController::class)
    ->except(['create']);
});

// TRANSPORTE (permiso_6)
Route::middleware(['auth', 'permission:permiso_6'])->group(function () {
  Route::resource('/transporte', TransporteController::class);

  Route::get('/transporte/{transporte}/transporte_personal/create', [TransportePersonalController::class, 'create'])
    ->name('transporte_personal.create');
  Route::resource('/transporte/transporte_personal', TransportePersonalController::class)
    ->except(['create']);
});

// EXPEDICION - Central de Pedidos (permiso_7)
Route::middleware(['auth', 'permission:permiso_7'])->group(function () {
  // Tablero
  Route::get('/expedicion', [ExpedicionController::class, 'index'])->name('expedicion.index');

  // Pedidos
  Route::get('/expedicion_pedido/condiciones', [ExpedicionPedidoController::class, 'condiciones'])->name('expedicion_pedido.condiciones');
  Route::post('/expedicion_pedido/{pedido}/confirmar', [ExpedicionPedidoController::class, 'confirmar'])->name('expedicion_pedido.confirmar');
  Route::post('/expedicion_pedido/{pedido}/estado', [ExpedicionPedidoController::class, 'estado'])->name('expedicion_pedido.estado');
  Route::get('/expedicion_pedido/{pedido}/planilla', [ExpedicionPedidoController::class, 'planilla'])->name('expedicion_pedido.planilla');
  Route::post('/expedicion_pedido/{pedido}/enviar', [ExpedicionPedidoController::class, 'enviar'])->name('expedicion_pedido.enviar');
  Route::resource('/expedicion_pedido', ExpedicionPedidoController::class);

  // Planificación semanal, control de entregas y Diaria
  Route::get('/expedicion/planificacion', [ExpedicionPlanificacionController::class, 'index'])->name('expedicion_planificacion.index');
  Route::get('/expedicion/control', [ExpedicionControlController::class, 'index'])->name('expedicion_control.index');
  Route::put('/expedicion/control/{linea}', [ExpedicionControlController::class, 'update'])->name('expedicion_control.update');
  Route::get('/expedicion/diaria', [ExpedicionDiariaController::class, 'index'])->name('expedicion_diaria.index');

  // Informes
  Route::get('/expedicion/informes/tolvas', [ExpedicionInformeController::class, 'tolvas'])->name('expedicion_informes.tolvas');
  Route::get('/expedicion/informes/certificados', [ExpedicionInformeController::class, 'certificados'])->name('expedicion_informes.certificados');

  // Avisos (destinatarios de mail/WhatsApp)
  Route::get('/expedicion/avisos', [ExpedicionNotificacionController::class, 'index'])->name('expedicion_avisos.index');
  Route::post('/expedicion/avisos', [ExpedicionNotificacionController::class, 'store'])->name('expedicion_avisos.store');
  Route::patch('/expedicion/avisos/{destinatario}', [ExpedicionNotificacionController::class, 'update'])->name('expedicion_avisos.update');
  Route::delete('/expedicion/avisos/{destinatario}', [ExpedicionNotificacionController::class, 'destroy'])->name('expedicion_avisos.destroy');

  // Maestros y configuración
  Route::resource('/expedicion_cliente', ExpedicionClienteController::class);
  Route::resource('/expedicion_molino', ExpedicionMolinoController::class);
  Route::resource('/expedicion_MTC', ExpedicionMolinoTextoClienteController::class);
  Route::get('/expedicion/fichas', [FichasController::class, 'index'])->name('expedicion_fichas.index');

  // Calidad (General)
  Route::get('/expedicion-general/export', [ExpedicionGeneralController::class, 'export'])->name('expedicion_general.export');
  Route::resource('/expedicion_general', ExpedicionGeneralController::class);
});

// AGENDA GENERAL (permiso_8)
Route::middleware(['auth', 'permission:permiso_8'])->resource('/AgendaGral', AgendaGralController::class);

// TOOLS (permiso_9)
Route::middleware(['auth', 'permission:permiso_9'])->group(function () {
  Route::resource('/tools/usuarios', UsuarioController::class);
  Route::put('/tools/usuarios/{id}/activate', [UsuarioController::class, 'activate'])->name('usuarios.activate');
  Route::resource('/tools/roles', RoleController::class);

  Route::resource('/tools/calles', CalleController::class);
  Route::resource('/tools/barrios', BarrioController::class);
  Route::resource('/tools/ciudades_municipios', MunicipioController::class);
  Route::resource('/tools/localidades', LocalidadController::class);
  Route::resource('/tools/zonas', ZonaController::class);
  Route::resource('/tools/familias', FamiliaController::class);
  Route::resource('/tools/rubros', RubroController::class);
  Route::resource('/tools/cargos', CargoController::class);
  Route::resource('/tools/modos', ModoController::class);
  Route::resource('/tools/dimension', TamanioController::class)->names('tamanios');
  Route::resource('/tools/areas', AreaController::class);
  Route::resource('/tools/horas', HoraController::class);
  Route::resource('/tools/contacto_inicial', Contacto_inicialController::class);
  Route::resource('/tools/prioridades', PrioridadController::class);
  Route::resource('/tools/estados', EstadoController::class);
  Route::resource('/tools/tipo_persona', Tipo_PersonaController::class);
  Route::resource('/tools/acciones', AccionController::class);
  Route::resource('/tools/veraz', VerazController::class);
  Route::resource('/tools/tareas', TareaController::class);

  // Exportar tablas
  Route::get('/tools/export', [ExportarController::class, 'selectTables'])->name('export.selectTables');
  Route::post('/export/generate', [ExportarController::class, 'generate'])->name('export.generate');
});

// PERMISOS Y AUDITORIA (solo Super Usuario)
Route::middleware(['auth', 'permission:permiso_99'])->group(function () {
  Route::resource('/tools/permissions', PermissionController::class);
  Route::get('/tools/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
  Route::get('/tools/auditoria/export', [AuditoriaController::class, 'export'])->name('auditoria.export');
});

require __DIR__ . '/auth.php';
