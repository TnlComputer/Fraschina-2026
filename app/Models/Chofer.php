<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chofer extends Model
{
  use SoftDeletes;

  protected $table = 'choferes';

  protected $guarded = [];

  /** El código se guarda en mayúsculas, como se escribe en Reparto. */
  protected function codigo(): Attribute
  {
    return Attribute::make(set: fn($valor) => mb_strtoupper(trim((string) $valor)));
  }
}
