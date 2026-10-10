<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepresentacionPersonal extends Model
{
  use HasFactory;

  protected $table = 'representacion_personal';
  
  protected $guarded = [];

  public function representacion()
  {
    return $this->belongsTo(Representacion::class, 'representacion_id');
  }

  public function area()
  {
    return $this->belongsTo(AuxAreas::class, 'area_id')->withTrashed();
  }

  public function cargo()
  {
    return $this->belongsTo(AuxCargos::class, 'cargo_id')->withTrashed();
  }

  public function profesion()
  {
    return $this->belongsTo(AuxProfesion::class, 'profesion_id')->withTrashed();
  }
}