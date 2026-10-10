<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProveedorPersonal extends Model
{
  use HasFactory;

  protected $table = 'proveedor_personal';

  protected $guarded = [];

  public function proveedor()
  {
    return $this->belongsTo(Proveedor::class, 'proveedor_id');
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
