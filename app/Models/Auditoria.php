<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Registro de una operación (log de auditoría). Solo lectura una vez creado.
 */
class Auditoria extends Model
{
  public const ACCIONES = [
    'alta' => 'Alta',
    'modificacion' => 'Modificación',
    'eliminacion' => 'Eliminación',
    'restauracion' => 'Restauración',
  ];

  public const UPDATED_AT = null;

  protected $table = 'auditoria';

  protected $guarded = [];

  protected $casts = [
    'cambios' => 'array',
    'created_at' => 'datetime',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function getAccionNombreAttribute(): string
  {
    return self::ACCIONES[$this->accion] ?? $this->accion;
  }
}
