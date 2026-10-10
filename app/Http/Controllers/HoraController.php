<?php

namespace App\Http\Controllers;

use App\Models\AuxHoras;
use App\Http\Controllers\Concerns\CrudAuxiliar;
use Illuminate\Http\Request;

class HoraController extends Controller
{
  use CrudAuxiliar;

  protected function auxiliar(): array
  {
    return [
      'modelo' => AuxHoras::class,
      'titulo' => 'Hora',
      'femenino' => true,
      'ruta' => 'horas',
      'campos' => [
        'hora' => ['label' => 'Hora', 'tipo' => 'time', 'requerido' => true, 'unico' => true],
      ],
      'usado_en' => [],
    ];
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $name = trim($request->get('name'));
    $sortField = $request->get('sort', 'hora'); // Campo por defecto

    // Comienza la consulta base
    $query = AuxHoras::query();

    // Si hay un filtro de nombre, se agrega la condición
    if ($name) {
      $query->where('hora', 'like', '%' . $name . '%');
    }

    // Aplica la paginación después de construir la consulta
    $horas = $query->orderBy($sortField)->paginate(15);
    // Retorna la vista con los datos
    return view('pages.Tools.Hora.index', compact('horas', 'name'));
  }

}
