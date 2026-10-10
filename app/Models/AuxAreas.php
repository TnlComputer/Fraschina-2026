<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Prompts\Table;
use tidy;

class AuxAreas extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'auxareas';

  protected $guarded = [];
}
