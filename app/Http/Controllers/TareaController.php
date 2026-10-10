<?php

namespace App\Http\Controllers;

use App\Models\DistribucionTareas;
use App\Http\Controllers\Concerns\CrudAuxiliar;
use Illuminate\Http\Request;

class TareaController extends Controller
{
  use CrudAuxiliar;

  protected function auxiliar(): array
  {
    return [
      'modelo' => DistribucionTareas::class,
      'titulo' => 'Tarea',
      'femenino' => true,
      'ruta' => 'tareas',
      'campos' => [
        'tarea' => ['label' => 'Tarea', 'requerido' => true, 'unico' => true],
      ],
      'usado_en' => ['distribucion_linea_tareas.tarea_id'],
    ];
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $name = trim($request->get('name'));
    $sortField = $request->get('sort', 'tarea'); // Campo por defecto

    // Comienza la consulta base
    $query = DistribucionTareas::query();

    // Si hay un filtro de nombre, se agrega la condición
    if ($name) {
      $query->where('tarea', 'like', '%' . $name . '%');
    }

    // Aplica la paginación después de construir la consulta
    $tareas = $query->orderBy($sortField)->paginate(15);

    // Retorna la vista con los datos
    return view('pages.Tools.Tarea.index', compact('tareas', 'name'));
  }

}
