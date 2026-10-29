<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deposito extends Model
{
  use SoftDeletes;

  protected $table = 'depositos';

  protected $guarded = [];

  protected $casts = [
    'principal' => 'boolean',
    'latitud' => 'float',
    'longitud' => 'float',
  ];

  /** El depósito desde donde sale el reparto. */
  public static function principal(): ?self
  {
    return static::where('principal', true)->first() ?? static::orderBy('id')->first();
  }

  public function tieneUbicacion(): bool
  {
    return $this->latitud !== null && $this->longitud !== null;
  }

  public function urlMapa(): ?string
  {
    return $this->tieneUbicacion()
      ? "https://www.openstreetmap.org/?mlat={$this->latitud}&mlon={$this->longitud}#map=17/{$this->latitud}/{$this->longitud}"
      : null;
  }
}
