<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Configuración de una combinación cliente-molino: condiciones habituales
 * (plazo, forma de pago, condición, planta), checklist de la planilla de pedido
 * y textos de pie propios de esa combinación.
 */
class ExpedicionMolinoTextoCliente extends Model
{
  use HasFactory;

  protected $table = 'expedicion_molinos_textos_clientes';

  protected $guarded = [];

  protected $casts = [
    'mostrar_precio' => 'boolean',
    'subrayar_precio' => 'boolean',
    'mostrar_fecha_alternativa' => 'boolean',
    'mostrar_especiales' => 'boolean',
  ];

  public function cliente()
  {
    return $this->belongsTo(ExpedicionCliente::class, 'expClie_id');
  }

  public function molino()
  {
    return $this->belongsTo(ExpedicionMolino::class, 'expMolino_id');
  }

  public static function para(int $clienteId, int $molinoId): ?self
  {
    return static::where('expClie_id', $clienteId)->where('expMolino_id', $molinoId)->first();
  }

  public function lineasTexto(): array
  {
    return array_values(array_filter(array_map(fn($i) => trim((string) $this->{'linea' . $i}), range(1, 6))));
  }
}
