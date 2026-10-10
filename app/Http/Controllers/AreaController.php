<?php

namespace App\Http\Controllers;

use App\Models\AuxAreas;
use App\Http\Controllers\Concerns\CrudAuxiliar;
use Illuminate\Http\Request;

class AreaController extends Controller
{
  use CrudAuxiliar;

  protected function auxiliar(): array
  {
    return [
      'modelo' => AuxAreas::class,
      'titulo' => 'Área',
      'femenino' => true,
      'ruta' => 'areas',
      'campos' => [
        'area' => ['label' => 'Área', 'requerido' => true, 'unico' => true],
      ],
      'usado_en' => ['agro_personal.area_id', 'distribucion_personal.area_id', 'molino_personal.area_id', 'proveedor_personal.area_id', 'representacion_personal.area_id', 'transporte_personal.area_id'],
    ];
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $name = trim($request->get('name'));
    $sortField = $request->get('sort', 'area'); // Campo por defecto

    // Comienza la consulta base
    $query = AuxAreas::query();

    // Si hay un filtro de nombre, se agrega la condición
    if ($name) {
      $query->where('area', 'like', '%' . $name . '%');
    }

    // Aplica la paginación después de construir la consulta
    $areas = $query->orderBy($sortField)->paginate(15);
    // Retorna la vista con los datos
    return view('pages.Tools.Area.index', compact('areas', 'name'));
  }

}
