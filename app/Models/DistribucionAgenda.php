<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistribucionAgenda extends Model
{
  use HasFactory;

  protected $table = 'distribucion_agenda';

  protected $fillable = [
    'distribucion_id',
    'fecha',
    'hs',
    'prioridad_id',
    'accion_id',
    'persona_id',
    'cotizacion',
    'fecCot',
    'temas',
    'status',
  ];

  // Relación con la tabla de prioridades (auxprioridades)
  public function auxprioridades()
  {
    return $this->belongsTo(AuxPrioridades::class, 'prioridad_id', 'id')->withTrashed();
  }
  // Relación con la tabla de acciones (auxacciones)
  public function auxacciones()
  {
    return $this->belongsTo(AuxAcciones::class, 'accion_id', 'id')->withTrashed();
  }
  // Relación con la distribución
  public function distribucion()
  {
    return $this->belongsTo(Distribucion::class, 'distribucion_id', 'id');
  }
  // Relación con productos CDA
  public function productocda()
  {
    return $this->belongsTo(productoCDA::class, 'producto_id', 'id');
  }
  // Relación con el personal de distribución
  public function distribucionPersonal()
  {
    return $this->belongsTo(DistribucionPersonal::class, 'persona_id', 'id');
  }
  public function distribucionNropedidos()
  {
    return $this->belongsTo(DistribucionNroPedidos::class, 'pedido_id', 'id');
  }
}
