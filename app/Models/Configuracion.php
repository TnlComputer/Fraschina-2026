<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Un parámetro de la app (clave / valor). Ver App\Services\Configuraciones.
 */
class Configuracion extends Model
{
  protected $table = 'configuraciones';

  protected $fillable = ['clave', 'valor'];
}
