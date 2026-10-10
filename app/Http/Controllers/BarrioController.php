<?php

namespace App\Http\Controllers;

use App\Models\AuxBarrios;
use App\Http\Controllers\Concerns\CrudAuxiliar;
use Illuminate\Http\Request;

class BarrioController extends Controller
{
  use CrudAuxiliar;

  protected function auxiliar(): array
  {
    return [
      'modelo' => AuxBarrios::class,
      'titulo' => 'Barrio',
      'ruta' => 'barrios',
      'campos' => [
        'nombrebarrio' => ['label' => 'Barrio', 'requerido' => true, 'unico' => true],
      ],
      'usado_en' => ['agros.barrio_id', 'distribucion_agenda.barrio_id', 'distribucions.barrio_id', 'molinos.barrio_id', 'proveedores.barrio_id', 'representacions.barrio_id', 'transportes.barrio_id'],
    ];
  }

    /**
     * Display a listing of the resource.
     */
  public function index(Request $request)
  {
    $name = trim($request->get('name'));
    $sortField = $request->get('sort', 'nombrebarrio'); // Campo por defecto

    // Comienza la consulta base
    $query = AuxBarrios::query();

    // Si hay un filtro de nombre, se agrega la condición
    if ($name) {
      $query->where('nombrebarrio', 'like', '%' . $name . '%');
    }

    // Aplica la paginación después de construir la consulta
    $barrios = $query->orderBy($sortField)->paginate(15);

    // Retorna la vista con los datos
    return view('pages.Tools.Barrio.index', compact('barrios', 'name'));
  }

}
