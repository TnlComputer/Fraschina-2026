<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpPedidoLinea extends Model
{
  use SoftDeletes;

  protected $table = 'exp_pedido_lineas';

  protected $guarded = [];

  protected $casts = [
    'fecha_entrega' => 'date',
    'fecha_alternativa' => 'date',
    'fecha_tentativa' => 'boolean',
    'precio' => 'decimal:3',
  ];

  /**
   * Campos que se copian al crear una revisión o comparar cambios.
   */
  public const CAMPOS = [
    'linea', 'calidad', 'tolvas', 'bolsas', 'kilos', 'precio', 'condicion', 'planta',
    'fecha_entrega', 'fecha_tentativa', 'nota_fecha', 'fecha_alternativa', 'hora',
    'plazo_dias', 'forma_pago', 'lugar_entrega', 'observaciones',
  ];

  public function pedido()
  {
    return $this->belongsTo(ExpPedido::class, 'pedido_id');
  }

  public function entrega()
  {
    return $this->hasOne(ExpEntrega::class, 'pedido_linea_id');
  }

  public function getEnvaseAttribute(): string
  {
    $partes = [];
    if ($this->tolvas) {
      $partes[] = $this->tolvas . ' tolva' . ($this->tolvas > 1 ? 's' : '');
    }
    if ($this->bolsas) {
      $partes[] = $this->bolsas . ' bolsa' . ($this->bolsas > 1 ? 's' : '');
    }
    return implode(' + ', $partes);
  }
}
