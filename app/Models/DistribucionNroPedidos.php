<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistribucionNroPedidos extends Model
{
  use HasFactory;
  protected $table = 'distribucion_nropedidos';

  protected $guarded = [];

  /**
   * Pedidos con al menos un producto o una tarea. Los pedidos sin líneas (en el sistema anterior
   * se les borraban todas) no van al reparto, aunque se conservan en la base.
   */
  public function scopeConLineas($query)
  {
    return $query->where(fn($q) => $q->whereHas('lineasPedidos')->orWhereHas('lineasTareas'));
  }

  /**
   * Orden del reparto, igual que el sistema anterior: chofer, orden de entrega y número de pedido.
   * Vale también para pedidos de solo tareas (el chofer y el orden son del pedido).
   * Los pedidos todavía sin chofer u orden van primero, pendientes de asignar.
   */
  public function scopeOrdenReparto($query)
  {
    return $query
      ->orderByRaw("COALESCE(chofer, '')")
      ->orderByRaw('COALESCE(orden, 0)')
      ->orderBy('id');
  }

  // public function lineasPedidos()
  // {
  //   return $this->hasMany(DistribucionLineaPedidos::class, 'pedido_id');
  // }

  // public function lineasTareas()
  // {
  //   return $this->hasMany(DistribucionLineaTareas::class, 'pedido_id');
  // }

  public function lineasPedidos()
  {
    return $this->hasMany(DistribucionLineaPedidos::class, 'pedido_id', 'id');
    // ->where('distribucion_id', $this->distribucion_id);
  }

  public function lineasTareas()
  {
    return $this->hasMany(DistribucionLineaTareas::class, 'pedido_id', 'id');
    // ->where('distribucion_id', $this->distribucion_id);
  }

  public function distribucion()
  {
    return $this->belongsTo(Distribucion::class, 'distribucion_id');  // Relación con la tabla distribucions
  }
}