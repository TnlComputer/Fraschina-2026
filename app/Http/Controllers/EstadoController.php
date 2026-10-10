<?php

namespace App\Http\Controllers;

use App\Models\AuxEstados;
use App\Http\Controllers\Concerns\CrudAuxiliar;
use Illuminate\Http\Request;

class EstadoController extends Controller
{
  use CrudAuxiliar;

  protected function auxiliar(): array
  {
    return [
      'modelo' => AuxEstados::class,
      'titulo' => 'Estado',
      'ruta' => 'estados',
      'campos' => [
        'nomEstado' => ['label' => 'Estado', 'requerido' => true, 'unico' => true],
      ],
      'usado_en' => ['distribucion_agenda.estado_id', 'distribucions.estado_id'],
    ];
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $name = trim($request->get('name'));
    $sortField = $request->get('sort', 'nomEstado'); // Campo por defecto

    // Comienza la consulta base
    $query = AuxEstados::query();

    // Si hay un filtro de nombre, se agrega la condición
    if ($name) {
      $query->where('nomEstado', 'like', '%' . $name . '%');
    }

    // Aplica la paginación después de construir la consulta
    $estados = $query->orderBy($sortField)->paginate(15);
    // Retorna la vista con los datos
    return view('pages.Tools.Estado.index', compact('estados', 'name'));
  }

}
