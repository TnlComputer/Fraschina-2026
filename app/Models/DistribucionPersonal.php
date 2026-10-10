<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistribucionPersonal extends Model
{
  use HasFactory;

  protected $table = 'distribucion_personal';

  protected $fillable = [
    'nombre',
    'apellido',
    'distribucion_id',
    'area_id',
    'cargo_id',
    'profesion_id',
    'teldirecto',
    'interno',
    'telcelular',
    'telparticular',
    'email',
    'observaciones',
    'fuera',
    'status',
  ];

  public function distribucion()
  {
    return $this->belongsTo(Distribucion::class, 'distribucion_id');
  }

  // public function area()
  // {
  //   return $this->belongsTo(AuxAreas::class, 'area_id');
  // }

  public function cargo()
  {
    return $this->belongsTo(AuxCargos::class, 'cargo_id')->withTrashed();
  }

  public function profesion()
  {
    return $this->belongsTo(AuxProfesion::class, 'profesion_id')->withTrashed();
  }
  public function tipoPersonal()
  {
    return $this->belongsTo(AuxTipoPersonal::class, 'tipo_personal_id')->withTrashed();  // Cambia 'tipo_personal_id' por el nombre correcto de la clave foránea
  }
}