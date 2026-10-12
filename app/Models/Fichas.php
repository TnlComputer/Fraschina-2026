<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Fichas: movimientos históricos de las planillas por cliente (cuenta corriente), importados de Fichas.xlsx.
 * origen_tabla = hoja del Excel; fila = fila de origen; extra = columnas adicionales y valores originales.
 */
class Fichas extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'fichas';

  protected $guarded = [];

  protected $casts = [
    'fe' => 'date',
    'fp' => 'date',
    'extra' => 'array',
  ];
}
