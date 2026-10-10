<?php

namespace App\Http\Controllers;

use App\Models\AuxAcciones;
use App\Http\Controllers\Concerns\CrudAuxiliar;
use Illuminate\Http\Request;

class AccionController extends Controller
{
  use CrudAuxiliar;

  protected function auxiliar(): array
  {
    return [
      'modelo' => AuxAcciones::class,
      'titulo' => 'Acción',
      'femenino' => true,
      'ruta' => 'acciones',
      'campos' => [
        'accion' => ['label' => 'Acción', 'requerido' => true, 'unico' => true],
        'colorAcc' => ['label' => 'Color', 'tipo' => 'color'],
      ],
      'usado_en' => ['distribucion_agenda.accion_id'],
    ];
  }

    /**
     * Display a listing of the resource.
     */
  public function index(Request $request)
  {
    $name = trim($request->get('name'));
    $sortField = $request->get('sort', 'accion'); // Campo por defecto

    // Comienza la consulta base
    $query = AuxAcciones::query();

    // Si hay un filtro de nombre, se agrega la condición
    if ($name) {
      $query->where('accion', 'like', '%' . $name . '%');
    }

    // Aplica la paginación después de construir la consulta
    $acciones = $query->orderBy($sortField)->paginate(15);
    // Retorna la vista con los datos
    return view('pages.Tools.Accion.index', compact('acciones', 'name'));
  }

}
