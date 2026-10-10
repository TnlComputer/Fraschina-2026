<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpPedidoMovimiento extends Model
{
  public const ACCIONES = [
    'creado' => 'Creado',
    'confirmado' => 'Confirmado',
    'modificado' => 'Modificado',
    'revision' => 'Nueva revisión',
    'enviado' => 'Enviado al molino',
    'estado' => 'Cambio de estado',
    'anulado' => 'Anulado',
    'entrega' => 'Entrega actualizada',
  ];

  public const UPDATED_AT = null;

  protected $table = 'exp_pedido_movimientos';

  protected $guarded = [];

  protected $casts = [
    'created_at' => 'datetime',
  ];

  public function pedido()
  {
    return $this->belongsTo(ExpPedido::class, 'pedido_id');
  }

  public function usuario()
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  public function getAccionNombreAttribute(): string
  {
    return self::ACCIONES[$this->accion] ?? $this->accion;
  }
}
