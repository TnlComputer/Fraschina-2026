<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpedicionCliente extends Model
{
  use HasFactory;

  protected $table = 'expedicion_clientes';

  protected $guarded = [];

  public function representacion()
  {
    return $this->belongsTo(Representacion::class, 'representacion_id');
  }

  public function pedidos()
  {
    return $this->hasMany(ExpPedido::class, 'expedicion_cliente_id');
  }

  /**
   * Nombre para mostrar: sigla y razón social del cliente (si está vinculado).
   */
  public function getNombreAttribute(): string
  {
    $razon = $this->representacion?->razonsocial;
    return trim($this->sigla . ($razon ? ' - ' . $razon : ''));
  }

  /**
   * Textos de pie de la planilla de pedido (linea1..linea6) sin vacíos.
   */
  public function lineasTexto(): array
  {
    return array_values(array_filter(array_map(fn($i) => trim((string) $this->{'linea' . $i}), range(1, 6))));
  }
}
