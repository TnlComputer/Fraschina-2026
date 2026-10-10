<?php

namespace App\Http\Controllers;

use App\Models\AuxTamanios;
use App\Http\Controllers\Concerns\CrudAuxiliar;
use Illuminate\Http\Request;

class TamanioController extends Controller
{
  use CrudAuxiliar;

  protected function auxiliar(): array
  {
    return [
      'modelo' => AuxTamanios::class,
      'titulo' => 'Dimensión',
      'femenino' => true,
      'ruta' => 'tamanios',
      'campos' => [
        'nombre' => ['label' => 'Dimensión', 'requerido' => true, 'unico' => true],
      ],
      'usado_en' => ['distribucion_agenda.tamanio_id', 'distribucions.tamanio_id'],
    ];
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $name = trim($request->get('name'));

    // Comienza la consulta base
    $query = AuxTamanios::query();

    // Si hay un filtro de nombre, se agrega la condición
    if ($name) {
      $query->where('nombre', 'like', '%' . $name . '%');
    }

    // Aplica la paginación después de construir la consulta
    $tamanios = $query->paginate(15);

    // Retorna la vista con los datos
    return view('pages.Tools.Tamanio.index', compact('tamanios', 'name'));
  }

}
