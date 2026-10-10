<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistribucionLineaPedidos extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'distribucion_linea_pedidos';

  protected $guarded = [];

  public function pedido()
  {
    return $this->belongsTo(DistribucionNroPedidos::class, 'pedido_id');
  }

  public function distribucion()
  {
    return $this->belongsTo(Distribucion::class, 'distribucion_id');
  }

  public function producto()
  {
    return $this->belongsTo(productoCDA::class, 'producto_id');  // Ajusta 'producto_id' si es necesario
  }
}
