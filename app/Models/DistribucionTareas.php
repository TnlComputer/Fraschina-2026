<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistribucionTareas extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'distribucion_tareas';

  protected $fillable = [
    'tarea',
    'status'
  ];

  // Relación: una tarea tiene muchas líneas de tarea
  public function tarea()
  {
    return $this->hasMany(DistribucionLineaTareas::class, 'tarea_id', 'id');
  }
}
