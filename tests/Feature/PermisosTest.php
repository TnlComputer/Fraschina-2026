<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Permission;
use Tests\TestCase;

class PermisosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['permiso_2', 'permiso_7', 'permiso_9', 'permiso_99'] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }

    public function test_invitado_no_puede_ver_impresiones_de_reparto(): void
    {
        $this->get('/distribucion/reparto/imprimir')->assertRedirect('/login');
        $this->get('/distribucion/reparto/control')->assertRedirect('/login');
        $this->get('/distribucion/pedidos/1/imprimirRecibo')->assertRedirect('/login');
        $this->get('/productos-y-tareas-por-cliente/1')->assertRedirect('/login');
    }

    public function test_usuario_sin_permiso_de_seccion_recibe_403(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('permiso_7');

        $this->actingAs($user)->get('/tools/usuarios')->assertForbidden();
        $this->actingAs($user)->get('/tools/permissions')->assertForbidden();
        $this->actingAs($user)->post('/export/generate', ['table' => 'users'])->assertForbidden();
    }

    public function test_tools_no_exporta_tablas_fuera_de_la_lista(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('permiso_9');

        $this->actingAs($user)
            ->from('/tools/export')
            ->post('/export/generate', ['table' => 'users'])
            ->assertRedirect('/tools/export')
            ->assertSessionHasErrors('table');
    }

    public function test_roles_y_permisos_se_eliminan_logicamente(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('permiso_99');
        \App\Models\Role::create(['name' => 'predeterminado', 'guard_name' => 'web']);
        $rol = \App\Models\Role::create(['name' => 'temporal', 'guard_name' => 'web']);
        $permiso = Permission::create(['name' => 'permiso_temporal', 'guard_name' => 'web']);

        $this->actingAs($admin)->delete("/tools/roles/{$rol->id}")->assertRedirect();
        $this->actingAs($admin)->delete("/tools/permissions/{$permiso->id}")->assertRedirect();

        $this->assertSoftDeleted('roles', ['id' => $rol->id]);
        $this->assertSoftDeleted('permissions', ['id' => $permiso->id]);
    }

    public function test_super_usuario_pasa_todos_los_permisos(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('permiso_99');

        $this->assertTrue($user->can('permiso_2'));
        $this->assertTrue($user->can('permiso_12'));
    }
}
