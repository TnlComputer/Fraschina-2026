<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\CrudAuxiliar;
use App\Models\Chofer;

class ChoferController extends Controller
{
  use CrudAuxiliar;

  protected function auxiliar(): array
  {
    return [
      'modelo' => Chofer::class,
      'titulo' => 'Chofer',
      'ruta' => 'choferes',
      'campos' => [
        'codigo' => ['label' => 'Código (el que se escribe en Reparto)', 'requerido' => true, 'unico' => true, 'max' => 2],
        'nombre' => ['label' => 'Nombre', 'requerido' => true, 'max' => 100],
        'telefono' => ['label' => 'Teléfono', 'max' => 50],
      ],
    ];
  }

  public function index()
  {
    $choferes = Chofer::orderBy('codigo')->get();
    return view('pages.Tools.Chofer.index', compact('choferes'));
  }
}
