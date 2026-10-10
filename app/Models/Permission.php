<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Permiso con borrado lógico: eliminar un permiso lo oculta, no lo borra de la base.
 */
class Permission extends SpatiePermission
{
  use SoftDeletes;
}
