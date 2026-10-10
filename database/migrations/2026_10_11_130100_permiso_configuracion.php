<?php

use App\Services\Auditoria;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * permiso_10 "Configuración": entra a Herramientas → Configuración. Lo tienen el rol supervisor
 * (y el Super Usuario por permiso_99). Es un permiso reservado: no se asigna desde el formulario de roles.
 */
return new class extends Migration
{
  public function up(): void
  {
    $id = DB::table('permissions')->where('name', 'permiso_10')->where('guard_name', 'web')->value('id');
    if (!$id) {
      $id = DB::table('permissions')->insertGetId([
        'name' => 'permiso_10', 'guard_name' => 'web', 'description' => 'Configuración', 'created_at' => now(), 'updated_at' => now(),
      ]);
    }

    $supervisor = DB::table('roles')->where('name', 'supervisor')->whereNull('deleted_at')->value('id');
    if ($supervisor && !DB::table('role_has_permissions')->where(['permission_id' => $id, 'role_id' => $supervisor])->exists()) {
      DB::table('role_has_permissions')->insert(['permission_id' => $id, 'role_id' => $supervisor]);
      app(Auditoria::class)->manual('modificacion', 'Usuarios y permisos', 'role_has_permissions', (string) $supervisor,
        'Rol supervisor: acceso a Configuración', ['permisos' => [null, 'permiso_10']]);
    }

    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
  }

  public function down(): void
  {
    // Queda en la auditoría; no se revierte automáticamente.
  }
};
