<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpEntrega extends Model
{
  protected $table = 'exp_entregas';

  protected $guarded = [];

  protected $casts = [
    'fecha_carga' => 'date',
    'certificado_fecha' => 'date',
    'descarga_completa' => 'boolean',
    'certificado_recibido' => 'boolean',
  ];

  public function linea()
  {
    return $this->belongsTo(ExpPedidoLinea::class, 'pedido_linea_id');
  }

  public function certificados()
  {
    return $this->hasMany(ExpedicionGeneral::class, 'entrega_id');
  }

  /**
   * Diferencia de kilos entre lo cargado en molino y lo recibido por el cliente.
   */
  public function getKgDifAttribute(): ?int
  {
    if ($this->kg_molino === null || $this->kg_cliente === null) {
      return null;
    }
    return (int) $this->kg_cliente - (int) $this->kg_molino;
  }
}
