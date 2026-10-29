<?php

use App\Services\Auditoria;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Los usuarios con el rol externo pasan a gerencia, que es el rol que les correspondía.
 * El rol externo queda sin usuarios (no se borra).
 */
return new class extends Migration
{
  public function up(): void
  {
    $externo = DB::table('roles')->where('name', 'externo')->whereNull('deleted_at')->value('id');
    $gerencia = DB::table('roles')->where('name', 'gerencia')->whereNull('deleted_at')->value('id');
    if (!$externo || !$gerencia) {
      return;
    }

    $asignaciones = DB::table('model_has_roles')->where('role_id', $externo)->get();
    foreach ($asignaciones as $a) {
      $yaGerencia = DB::table('model_has_roles')->where(['role_id' => $gerencia, 'model_type' => $a->model_type, 'model_id' => $a->model_id])->exists();
      $query = DB::table('model_has_roles')->where(['role_id' => $externo, 'model_type' => $a->model_type, 'model_id' => $a->model_id]);
      $yaGerencia ? $query->delete() : $query->update(['role_id' => $gerencia]);

      $nombre = DB::table('users')->where('id', $a->model_id)->value('name');
      app(Auditoria::class)->manual('modificacion', 'Usuarios y permisos', 'users', (string) $a->model_id,
        "Usuario {$nombre}: rol externo → gerencia", ['rol' => ['externo', 'gerencia']]);
    }

    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
  }

  public function down(): void
  {
    // Queda en la auditoría; no se revierte automáticamente.
  }
};
