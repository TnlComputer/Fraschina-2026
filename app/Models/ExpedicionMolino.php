<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpedicionMolino extends Model
{
  use HasFactory;

  protected $table = 'expedicion_molinos';

  protected $guarded = [];

  public function molino()
  {
    return $this->belongsTo(Molino::class, 'nroMolino_id');
  }

  public function pedidos()
  {
    return $this->hasMany(ExpPedido::class, 'expedicion_molino_id');
  }

  public function destinatarios()
  {
    return $this->hasMany(ExpNotificacionDestinatario::class, 'expedicion_molino_id');
  }

  /**
   * Nombre para mostrar: sigla y razón social del molino (si está vinculado).
   */
  public function getNombreAttribute(): string
  {
    $razon = $this->molino?->razonsocial;
    return trim($this->sigla . ($razon ? ' - ' . $razon : ''));
  }

  /**
   * calificacion = 1 indica que el precio se muestra en la planilla (sistema anterior).
   */
  public function muestraPrecio(): bool
  {
    return (string) $this->calificacion === '1';
  }
}
