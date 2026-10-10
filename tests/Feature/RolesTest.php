<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();
    foreach (['permiso_3', 'permiso_9', 'permiso_11', 'permiso_12', 'permiso_13', 'permiso_99'] as $p) {
      Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web'], ['description' => $p]);
    }
  }

  private function usuarioConRol(string $rol, array $permisos): User
  {
    $role = Role::firstOrCreate(['name' => $rol, 'guard_name' => 'web']);
    $role->syncPermissions($permisos);
    return User::factory()->create()->assignRole($role);
  }

  public function test_sin_permiso_de_alta_ni_edicion_solo_consulta(): void
  {
    $externo = $this->usuarioConRol('externo', ['permiso_3']);

    $this->actingAs($externo)->get('/molino')->assertOk()->assertDontSee(route('molino.create'));
    $this->actingAs($externo)->get('/molino/create')->assertForbidden()->assertSee('dar de alta');
    $this->actingAs($externo)->post('/molino', ['razonsocial' => 'X'])->assertForbidden();
    $this->actingAs($externo)->put('/molino/1', ['razonsocial' => 'X'])->assertForbidden();
    $this->actingAs($externo)->delete('/molino/1')->assertForbidden();
  }

  public function test_quien_gestiona_roles_no_puede_tocar_al_super_usuario(): void
  {
    $admin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
    $admin->syncPermissions(['permiso_99']);
    $gerente = $this->usuarioConRol('gerencia', ['permiso_9', 'permiso_11', 'permiso_12', 'permiso_13']);
    $super = User::factory()->create()->assignRole($admin);

    // No ve ni edita el rol admin
    $this->actingAs($gerente)->get('/tools/roles')->assertOk()->assertSee('gerencia')->assertDontSee('Super Usuario');
    $this->actingAs($gerente)->get("/tools/roles/{$admin->id}/edit")->assertForbidden();

    // No puede darse permiso_99 ni asignar el rol admin
    $super99 = Permission::where('name', 'permiso_99')->value('id');
    $gerencia = Role::where('name', 'gerencia')->first();
    $actuales = $gerencia->permissions->pluck('id')->all();
    $this->actingAs($gerente)->put("/tools/roles/{$gerencia->id}", ['name' => 'gerencia', 'permissions' => [...$actuales, $super99]]);
    $this->assertFalse($gerencia->fresh()->checkPermissionTo('permiso_99'));
    $this->assertTrue($gerencia->fresh()->checkPermissionTo('permiso_9'));
    $this->actingAs($gerente)->put("/tools/usuarios/{$gerente->id}", ['name' => $gerente->name, 'email' => $gerente->email, 'role' => $admin->id, 'is_active' => 1])
      ->assertSessionHasErrors('role');
    $this->actingAs($gerente)->get("/tools/usuarios/{$super->id}/edit")->assertForbidden();
  }

  public function test_el_super_usuario_conserva_su_permiso_al_editar_su_rol(): void
  {
    $admin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
    $admin->syncPermissions(['permiso_99']);
    $super = User::factory()->create()->assignRole($admin);

    $this->actingAs($super)->get("/tools/roles/{$admin->id}/edit")->assertOk()->assertSee('Super Usuario');
    $this->actingAs($super)->put("/tools/roles/{$admin->id}", ['name' => 'admin', 'permissions' => []])->assertRedirect('/tools/roles');
    $this->assertTrue($admin->fresh()->checkPermissionTo('permiso_99'));
  }

  public function test_rol_con_usuarios_no_se_elimina_y_el_vacio_queda_con_baja_logica(): void
  {
    $gerente = $this->usuarioConRol('gerencia', ['permiso_9', 'permiso_11', 'permiso_12', 'permiso_13']);
    $usado = Role::where('name', 'gerencia')->first();
    $vacio = Role::create(['name' => 'temporal', 'guard_name' => 'web']);

    $this->actingAs($gerente)->from('/tools/roles')->delete("/tools/roles/{$usado->id}")->assertSessionHas('error');
    $this->assertNull($usado->fresh()->deleted_at);

    $this->actingAs($gerente)->delete("/tools/roles/{$vacio->id}")->assertSessionHas('success');
    $this->assertSoftDeleted('roles', ['id' => $vacio->id]);
  }

  public function test_alta_de_rol_con_secciones_y_acciones(): void
  {
    $gerente = $this->usuarioConRol('gerencia', ['permiso_9', 'permiso_11', 'permiso_12', 'permiso_13']);
    $ids = Permission::whereIn('name', ['permiso_3', 'permiso_12'])->pluck('id')->all();

    $this->actingAs($gerente)->get('/tools/roles/create')->assertOk()->assertSee('Secciones')->assertSee('Acciones');
    $this->actingAs($gerente)->post('/tools/roles', ['name' => 'molinero', 'permissions' => $ids])->assertRedirect('/tools/roles');
    $this->assertEqualsCanonicalizing(['permiso_3', 'permiso_12'], Role::where('name', 'molinero')->first()->permissions->pluck('name')->all());
  }
}
