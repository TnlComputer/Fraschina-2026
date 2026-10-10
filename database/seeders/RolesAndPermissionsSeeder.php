<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
  public function run()
  {
    // Deshabilitar restricciones de claves foráneas temporalmente
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');

    // Limpiar tablas relacionadas con permisos y roles
    DB::table('model_has_permissions')->truncate();
    DB::table('role_has_permissions')->truncate();
    DB::table('permissions')->truncate();
    DB::table('roles')->truncate();

    // Crear permisos
    $permissions = [
      ['name' => 'permiso_1', 'description' => 'Representación'],
      ['name' => 'permiso_2', 'description' => 'Distribución'],
      ['name' => 'permiso_3', 'description' => 'Molino'],
      ['name' => 'permiso_4', 'description' => 'Agro'],
      ['name' => 'permiso_5', 'description' => 'Proveedores'],
      ['name' => 'permiso_6', 'description' => 'Transportes'],
      ['name' => 'permiso_7', 'description' => 'Expedición'],
      ['name' => 'permiso_8', 'description' => 'Agenda General'],
      ['name' => 'permiso_9', 'description' => 'Tools'],
      ['name' => 'permiso_11', 'description' => 'Alta registros'],
      ['name' => 'permiso_12', 'description' => 'Editar registros'],
      ['name' => 'permiso_13', 'description' => 'Eliminar registros'],
      ['name' => 'permiso_99', 'description' => 'Super Usuario'],
    ];

    foreach ($permissions as $permission) {
      Permission::firstOrCreate(
        ['name' => $permission['name']],
        [
          'guard_name' => 'web',
          'description' => $permission['description']
        ]
      );
    }

    // Crear roles
    $roles = [
      'admin' => array_column($permissions, 'name'),  // Admin tiene todos los permisos
      'supervisor' => [
        'permiso_1',
        'permiso_2',
        'permiso_3',
        'permiso_4',
        'permiso_5',
        'permiso_6',
        'permiso_7',
        'permiso_8',
        'permiso_9',
        'permiso_11',
        'permiso_12',
        'permiso_13'
      ],
      'gerencia' => ['permiso_1', 'permiso_2', 'permiso_3', 'permiso_4', 'permiso_5', 'permiso_6', 'permiso_7', 'permiso_8', 'permiso_9', 'permiso_11', 'permiso_12', 'permiso_13'],
      'distribucion' => ['permiso_2', 'permiso_3', 'permiso_5', 'permiso_8', 'permiso_11', 'permiso_12', 'permiso_13'],
      'administracion' => ['permiso_1', 'permiso_11', 'permiso_12', 'permiso_13'],
      'administrativo' => ['permiso_1', 'permiso_11', 'permiso_13'],
      'expedicion' => ['permiso_7', 'permiso_11', 'permiso_13'],
      'externo' => ['permiso_3', 'permiso_13'],
      'admindistrib' => ['permiso_1', 'permiso_2', 'permiso_3', 'permiso_5', 'permiso_8', 'permiso_11', 'permiso_12', 'permiso_13']
    ];

    // Crear roles y asignar permisos
    foreach ($roles as $roleName => $rolePermissions) {
      $role = Role::firstOrCreate(['name' => $roleName]);

      // Asignar los permisos al rol
      $role->syncPermissions($rolePermissions); // Esto asigna los permisos a cada rol
    }

    // Habilitar restricciones de claves foráneas nuevamente
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
  }
}