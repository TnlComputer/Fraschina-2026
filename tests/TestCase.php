<?php

namespace Tests;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Da al usuario los permisos de alta, edición y baja de registros (permiso_11/12/13).
     */
    protected function conAcciones(User $user): User
    {
        foreach (['permiso_11', 'permiso_12', 'permiso_13'] as $nombre) {
            Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
        }
        return $user->givePermissionTo(['permiso_11', 'permiso_12', 'permiso_13']);
    }
}
