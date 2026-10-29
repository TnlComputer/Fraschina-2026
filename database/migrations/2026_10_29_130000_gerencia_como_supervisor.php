<?php

use App\Services\Auditoria;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * El rol gerencia tiene el mismo acceso que el supervisor: suma los permisos reservados
 * permiso_10 (Configuración) y permiso_14 (Auditoría).
 */
return new class extends Migration
{
  public function up(): void
  {
    $gerencia = DB::table('roles')->where('name', 'gerencia')->whereNull('deleted_at')->value('id');
    if (!$gerencia) {
      return;
    }

    $agregados = [];
    foreach (['permiso_10', 'permiso_14'] as $nombre) {
      $id = DB::table('permissions')->where('name', $nombre)->where('guard_name', 'web')->value('id');
      if ($id && !DB::table('role_has_permissions')->where(['permission_id' => $id, 'role_id' => $gerencia])->exists()) {
        DB::table('role_has_permissions')->insert(['permission_id' => $id, 'role_id' => $gerencia]);
        $agregados[] = $nombre;
      }
    }

    if ($agregados) {
      app(Auditoria::class)->manual('modificacion', 'Usuarios y permisos', 'role_has_permissions', (string) $gerencia,
        'Rol gerencia: acceso a Configuración y Auditoría, igual que el supervisor', ['permisos' => [null, implode(', ', $agregados)]]);
    }

    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
  }

  public function down(): void
  {
    // Queda en la auditoría; no se revierte automáticamente.
  }
};
