<?php

namespace Tests\Feature;

use App\Models\Configuracion;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Configuraciones;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConfiguracionTest extends TestCase
{
  use RefreshDatabase;

  private array $datos = [
    'mail_modo' => 'prueba', 'mail_casilla_prueba' => 'pruebas@example.com',
    'mail_remitente' => 'pedidos@example.com', 'mail_remitente_nombre' => 'Fraschina - Control de Pedidos',
    'consultas_activas' => '1', 'consulta_manana' => '07:30', 'consulta_tarde' => '17:00', 'consulta_dias' => [1, 2, 3, 4, 5],
    'numero_inicial' => '5000', 'nombre_planilla' => 'Orden de Pedido', 'respaldos_dias' => '30',
  ];

  protected function setUp(): void
  {
    parent::setUp();
    foreach (['permiso_9', 'permiso_10', 'permiso_11', 'permiso_12', 'permiso_13', 'permiso_99'] as $p) {
      Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web'], ['description' => $p]);
    }
  }

  private function conRol(string $rol, array $permisos): User
  {
    $role = Role::firstOrCreate(['name' => $rol, 'guard_name' => 'web']);
    $role->syncPermissions($permisos);
    return User::factory()->create()->assignRole($role);
  }

  public function test_solo_administrador_y_supervisor_entran(): void
  {
    $gerente = $this->conRol('gerencia', ['permiso_9', 'permiso_11', 'permiso_12', 'permiso_13']);
    $supervisor = $this->conRol('supervisor', ['permiso_9', 'permiso_10']);
    $admin = $this->conRol('admin', ['permiso_99']);

    $this->actingAs($gerente)->get('/tools/configuracion')->assertForbidden();
    $this->actingAs($gerente)->put('/tools/configuracion', $this->datos)->assertForbidden();
    $this->actingAs($supervisor)->get('/tools/configuracion')->assertOk()->assertSee('Modo de envío');
    $this->actingAs($admin)->get('/tools/configuracion')->assertOk();
  }

  public function test_guarda_los_parametros_y_queda_en_la_auditoria(): void
  {
    $supervisor = $this->conRol('supervisor', ['permiso_9', 'permiso_10']);

    $this->actingAs($supervisor)->put('/tools/configuracion', $this->datos)->assertRedirect('/tools/configuracion')->assertSessionHas('success');

    $this->assertSame('prueba', Configuracion::where('clave', 'mail.modo')->value('valor'));
    $this->assertSame('1,2,3,4,5', Configuracion::where('clave', 'expedicion.consulta_dias')->value('valor'));
    $this->assertSame('5000', Configuracion::where('clave', 'expedicion.numero_inicial')->value('valor'));
    $this->assertTrue(DB::table('auditoria')->where('tabla', 'configuraciones')->exists());

    // Modo prueba sin casilla: no se acepta
    $this->actingAs($supervisor)->put('/tools/configuracion', array_merge($this->datos, ['mail_casilla_prueba' => '']))
      ->assertSessionHasErrors('mail_casilla_prueba');
  }

  public function test_modo_de_envio_de_correos(): void
  {
    config(['mail.default' => 'smtp', 'mail.mailer_env' => null]);
    app(Configuraciones::class)->aplicar();
    $this->assertSame('log', config('mail.default'));  // por defecto: no enviar

    Configuracion::create(['clave' => 'mail.modo', 'valor' => 'normal']);
    config(['mail.default' => 'smtp']);
    app(Configuraciones::class)->aplicar();
    $this->assertSame('smtp', config('mail.default'));

    Configuracion::where('clave', 'mail.modo')->update(['valor' => 'prueba']);  // sin casilla: no envía
    app(Configuraciones::class)->aplicar();
    $this->assertSame('log', config('mail.default'));
  }

  public function test_gerencia_no_puede_asignarse_el_rol_supervisor(): void
  {
    $gerente = $this->conRol('gerencia', ['permiso_9', 'permiso_11', 'permiso_12', 'permiso_13']);
    $supervisor = Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'web']);
    $supervisor->syncPermissions(['permiso_9', 'permiso_10']);

    $this->actingAs($gerente)->put("/tools/usuarios/{$gerente->id}", ['name' => $gerente->name, 'email' => $gerente->email, 'role' => $supervisor->id])
      ->assertSessionHasErrors('role');
    $this->actingAs($gerente)->get("/tools/roles/{$supervisor->id}/edit")->assertForbidden();
  }
}
