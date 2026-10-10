<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Rol con borrado lógico: eliminar un rol lo oculta, no lo borra de la base.
 */
class Role extends SpatieRole
{
  use SoftDeletes;
}
