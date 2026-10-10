<?php

namespace App\Http\Controllers;

use App\Models\AuxCalles;
use App\Http\Controllers\Concerns\CrudAuxiliar;
use Illuminate\Http\Request;

class CalleController extends Controller
{
  use CrudAuxiliar;

  protected function auxiliar(): array
  {
    return [
      'modelo' => AuxCalles::class,
      'titulo' => 'Calle',
      'femenino' => true,
      'ruta' => 'calles',
      'campos' => [
        'calle' => ['label' => 'Calle', 'requerido' => true, 'unico' => true],
      ],
      'usado_en' => ['distribucions.dire_calle_id'],
    ];
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $name = trim($request->get('name'));

    // Comienza la consulta base
    $query = AuxCalles::query();
    $sortField = $request->get('sort', 'calle'); // Campo por defecto

    // Si hay un filtro de nombre, se agrega la condición
    if ($name) {
      $query->where('calle', 'like', '%' . $name . '%');
    }

    // Ejecuta la consulta y agrega paginación
    $calles = $query->orderBy($sortField)->paginate(15);

    // Devuelve la vista con las calles y el filtro de búsqueda
    return view('pages.Tools.Calle.index', compact('calles', 'name'));
  }

}
