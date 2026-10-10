<?php

namespace App\Http\Controllers;

use App\Models\AuxPrioridades;
use App\Http\Controllers\Concerns\CrudAuxiliar;
use Illuminate\Http\Request;

class PrioridadController extends Controller
{
  use CrudAuxiliar;

  protected function auxiliar(): array
  {
    return [
      'modelo' => AuxPrioridades::class,
      'titulo' => 'Prioridad',
      'femenino' => true,
      'ruta' => 'prioridades',
      'campos' => [
        'nombre' => ['label' => 'Prioridad', 'requerido' => true, 'unico' => true],
        'color' => ['label' => 'Color', 'tipo' => 'color'],
      ],
      'usado_en' => ['distribucion_agenda.prioridad_id'],
    ];
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $name = trim($request->get('name'));
    $sortField = $request->get('sort', 'nombre'); // Campo por defecto

    // Comienza la consulta base
    $query = AuxPrioridades::query();

    // Si hay un filtro de nombre, se agrega la condición
    if ($name) {
      $query->where('nombre', 'like', '%' . $name . '%');
    }

    // Aplica la paginación después de construir la consulta
    $prioridades = $query->orderBy($sortField)->paginate(15);
    // Retorna la vista con los datos
    return view('pages.Tools.Prioridad.index', compact('prioridades', 'name'));
  }

}
