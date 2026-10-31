<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpedicionGeneral extends Model
{
  use HasFactory;

  protected $table = 'expedicion_general';

  protected $attributes = [
    'status' => 'A',
  ];

  protected $guarded = [];

  protected $casts = [
    'revisado' => 'boolean',
  ];

  /**
   * Mínimos y máximos de expedición general (planilla de codificación de clientes, Hoja3).
   * Se puede cargar fuera de rango; solo se marca en rojo. Punt rige para harinas 0000.
   */
  public const RANGOS = [
    'p' => [20, 200],
    'l' => [10, 300],
    'pl' => [0.15, 7],
    'w' => [42, 600],
    'gh' => [0, 45],
    'gs' => [0, 20],
    'gi' => [0, 100],
    'hum' => [8, 17],
    'cz' => [0.25, 1.2],
    'est' => [0, 30],
    'abs' => [40, 70],
    'fn' => [62, 600],
    'punt' => [0, 100],
  ];

  public function fueraDeRango(string $campo): bool
  {
    $valor = $this->{$campo};
    if ($valor === null || $valor === '' || !isset(self::RANGOS[$campo])) {
      return false;
    }
    [$min, $max] = self::RANGOS[$campo];
    return $valor < $min || $valor > $max;
  }

  /** Campos fuera de rango, en mayúsculas para mostrar. */
  public function camposFueraDeRango(): array
  {
    return array_map('strtoupper', array_values(array_filter(array_keys(self::RANGOS), fn($c) => $this->fueraDeRango($c))));
  }

  public function entrega()
  {
    return $this->belongsTo(ExpEntrega::class, 'entrega_id');
  }
}
