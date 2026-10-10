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

  public function entrega()
  {
    return $this->belongsTo(ExpEntrega::class, 'entrega_id');
  }
}
