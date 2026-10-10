<?php

namespace App\Http\Controllers;

use App\Models\AuxCargos;
use App\Http\Controllers\Concerns\CrudAuxiliar;
use Illuminate\Http\Request;

class CargoController extends Controller
{
  use CrudAuxiliar;

  protected function auxiliar(): array
  {
    return [
      'modelo' => AuxCargos::class,
      'titulo' => 'Cargo',
      'ruta' => 'cargos',
      'campos' => [
        'cargo' => ['label' => 'Cargo', 'requerido' => true, 'unico' => true],
        'categoria_id' => ['label' => 'Categoría (número)', 'tipo' => 'number'],
      ],
      'usado_en' => ['agro_personal.cargo_id', 'distribucion_agenda.cargo_id', 'distribucion_personal.cargo_id', 'molino_personal.cargo_id', 'proveedor_personal.cargo_id', 'representacion_personal.cargo_id', 'transporte_personal.cargo_id'],
    ];
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $name = trim($request->get('name'));
    $sortField = $request->get('sort', 'cargo'); // Campo por defecto

    // Comienza la consulta base
    $query = AuxCargos::query();

    // Si hay un filtro de nombre, se agrega la condición
    if ($name) {
      $query->where('cargo', 'like', '%' . $name . '%');
    }

    // Aplica la paginación después de construir la consulta
    $cargos = $query->orderBy($sortField)->paginate(15);
    // Retorna la vista con los datos
    return view('pages.Tools.Cargo.index', compact('cargos', 'name'));
  }

}
