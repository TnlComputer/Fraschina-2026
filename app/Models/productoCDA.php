<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class productoCDA extends Model
{
  use HasFactory;

  protected $table = 'productos_c_d_a';

  protected $fillable = [
    'productoCDA',
    'ivancda',
    'ivacda',
    'stockmincda',
    'stockmaxcda',
    'stockcda',
    'stockreservacda',
    'stockdisponiblecda',
    'stockfecentcda',
    'cantultent',
    'status',
  ];

  // Relación inversa con Distribucion_Producto
  public function distribuciones()
  {
    return $this->hasMany(DistribucionProducto::class, 'producto_id');
  }

  /**
   * Nombre para listas y selectores: el registro en blanco de la migración se muestra como "(sin dato)".
   */
  public function nombre(): string
  {
    return trim((string) $this->productoCDA) !== '' ? $this->productoCDA : '(sin dato)';
  }

  /**
   * Alícuota de IVA del producto. Igual que el sistema anterior: 10.5 si ivacda es 10.5, si no 21.
   */
  public function porcentajeIva(): float
  {
    return (float) str_replace(',', '.', (string) $this->ivacda) == 10.5 ? 10.5 : 21.0;
  }

  /**
   * Total de una línea de pedido con IVA incluido, redondeado a 2 decimales.
   */
  public static function calcularTotalLinea($precioUnitario, $cantidad, float $porcentajeIva): float
  {
    return round((float) $precioUnitario * (float) $cantidad * (1 + $porcentajeIva / 100), 2);
  }
}