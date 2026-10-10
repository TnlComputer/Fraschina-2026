<?php

use App\Services\Auditoria;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * El acceso de cada usuario pasa a definirlo solo su rol.
 *
 * Los usuarios traían permisos sueltos (asignados directamente, de la migración del sistema anterior)
 * además de su rol, y editar un rol no cambiaba lo que podían hacer. Se procede sin que nadie gane
 * ni pierda acceso:
 *  1. Si todos los usuarios de un rol tienen el mismo permiso suelto que el rol no tiene, se suma al rol.
 *  2. Se quitan los permisos sueltos que ya cubre el rol del usuario.
 *  3. Los que no se puedan cubrir así (caso de un solo usuario distinto a sus compañeros) se dejan.
 * Todo queda en la auditoría.
 */
return new class extends Migration
{
  public function up(): void
  {
    if (!Schema::hasTable('model_has_permissions') || !Schema::hasTable('model_has_roles')) {
      return;
    }
    $usuario = 'App\\Models\\User';
    $nombre = DB::table('permissions')->pluck('name', 'id');
    $directos = fn($uid) => DB::table('model_has_permissions')->where('model_type', $usuario)->where('model_id', $uid)->pluck('permission_id')->all();
    $deRol = fn($uid) => DB::table('model_has_roles as mr')->join('role_has_permissions as rp', 'rp.role_id', '=', 'mr.role_id')
      ->where('mr.model_type', $usuario)->where('mr.model_id', $uid)->pluck('rp.permission_id')->unique()->all();
    $efectivos = function ($uid) use ($directos, $deRol) {
      $p = array_unique(array_merge($directos($uid), $deRol($uid)));
      sort($p);
      return $p;
    };

    $usuarios = DB::table('users')->pluck('id');
    $antes = $usuarios->mapWithKeys(fn($uid) => [$uid => $efectivos($uid)]);

    DB::transaction(function () use ($usuario, $nombre, $directos, $deRol, $efectivos, $usuarios, $antes) {
      // 1. Permisos sueltos comunes a todos los usuarios de un rol → al rol
      foreach (DB::table('roles')->whereNull('deleted_at')->get() as $rol) {
        $miembros = DB::table('model_has_roles')->where('role_id', $rol->id)->where('model_type', $usuario)->pluck('model_id');
        if ($miembros->isEmpty()) {
          continue;
        }
        $comunes = null;
        foreach ($miembros as $uid) {
          $comunes = $comunes === null ? $directos($uid) : array_values(array_intersect($comunes, $directos($uid)));
        }
        $tiene = DB::table('role_has_permissions')->where('role_id', $rol->id)->pluck('permission_id')->all();
        $agregar = array_values(array_diff($comunes ?? [], $tiene));
        if ($agregar) {
          DB::table('role_has_permissions')->insert(array_map(fn($p) => ['permission_id' => $p, 'role_id' => $rol->id], $agregar));
          app(Auditoria::class)->manual('modificacion', 'Usuarios y permisos', 'role_has_permissions', (string) $rol->id,
            "Rol {$rol->name}: se suman los permisos que sus usuarios tenían sueltos",
            ['permisos' => [implode(', ', array_map(fn($p) => $nombre[$p] ?? $p, $tiene)), implode(', ', array_map(fn($p) => $nombre[$p] ?? $p, array_merge($tiene, $agregar)))]]);
        }
      }

      // 2. Quitar los permisos sueltos que ya cubre el rol
      foreach ($usuarios as $uid) {
        $cubiertos = array_values(array_intersect($directos($uid), $deRol($uid)));
        if (!$cubiertos) {
          continue;
        }
        DB::table('model_has_permissions')->where('model_type', $usuario)->where('model_id', $uid)->whereIn('permission_id', $cubiertos)->delete();
        app(Auditoria::class)->manual('modificacion', 'Usuarios y permisos', 'model_has_permissions', (string) $uid,
          'Permisos sueltos reemplazados por los de su rol (el acceso no cambia)',
          ['permisos_sueltos' => [implode(', ', array_map(fn($p) => $nombre[$p] ?? $p, $cubiertos)), null]]);
      }

      // Nadie puede ganar ni perder acceso
      foreach ($usuarios as $uid) {
        if ($efectivos($uid) !== $antes[$uid]) {
          throw new RuntimeException("El acceso del usuario $uid cambiaría; no se aplica ningún cambio.");
        }
      }
    });

    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
  }

  public function down(): void
  {
    // Los cambios quedan en la auditoría; no se revierten automáticamente.
  }
};
