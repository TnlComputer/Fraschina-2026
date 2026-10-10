<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuxProfesion extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'auxprofesiones';

  protected $fillable = [
    'nombreprofesion',
    'representaciones',
    'distribuciones',
    'molinos',
    'proveedores',
    'agros',
    'transportes',
    'agendas'
  ];

  public function agendas()
  {
    return $this->hasMany(AgendaGral::class, 'cod_prof');
  }
}
