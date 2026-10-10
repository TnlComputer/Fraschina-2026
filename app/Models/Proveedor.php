<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
  use HasFactory;

  protected $table = 'proveedores';

  protected $guarded = [];

  public function barrio()
  {
    return $this->belongsTo(AuxBarrios::class, 'barrio_id')->withTrashed();
  }

  public function localidad()
  {
    return $this->belongsTo(AuxLocalidades::class, 'localidad_id')->withTrashed();
  }

  public function municipio()
  {
    return $this->belongsTo(AuxMunicipios::class, 'municipio_id')->withTrashed();
  }

  public function rubro()
  {
    return $this->belongsTo(AuxRubros::class, 'rubro_id')->withTrashed();
  }

  public function personal()
  {
    return $this->hasMany(ProveedorPersonal::class, 'proveedor_id')->where('status', 'A');
  }

  public function productos()
  {
    return $this->hasMany(ProveedorProducto::class, 'proveedor_id')->where('status', 'A');
  }
}