<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaGral extends Model
{
  use HasFactory;

  protected $table = 'agendas';

  protected $fillable = [
    'nombre',
    'apellido',
    'nomApe',
    'empresa_institucion',
    'profesion_especialidad_oficio',
    'cod_prof',
    'tel_particular',
    'tel_laboral',
    'interno',
    'celular',
    'mail',
    'direccion',
    'observaciones',
    'buscador1',
    'buscador2',
    'buscador3',
    'status'
  ];

  public function profesion()
  {
    return $this->belongsTo(AuxProfesion::class, 'cod_prof')->withTrashed();
  }
}
