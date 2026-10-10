<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuxMunicipios extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'auxmunicipios';

  protected $guarded = [];
  
  public function representacions()
  {
    return $this->hasMany(Representacion::class);
  }
}