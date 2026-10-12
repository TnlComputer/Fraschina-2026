<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Duda de la migración de planillas (ver comando expedicion:dudas y Expedición → Dudas de migración).
 */
class MigracionDuda extends Model
{
  protected $table = 'migracion_dudas';

  protected $guarded = [];

  protected $casts = [
    'detalle' => 'array',
    'resuelta_at' => 'datetime',
  ];

  public function resolvio()
  {
    return $this->belongsTo(User::class, 'resuelta_por');
  }
}
