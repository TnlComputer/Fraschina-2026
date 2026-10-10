<?php

namespace App\Http\Controllers;

use App\Models\AuxModos;
use App\Http\Controllers\Concerns\CrudAuxiliar;
use Illuminate\Http\Request;

class ModoController extends Controller
{
  use CrudAuxiliar;

  protected function auxiliar(): array
  {
    return [
      'modelo' => AuxModos::class,
      'titulo' => 'Modo',
      'ruta' => 'modos',
      'campos' => [
        'nombre' => ['label' => 'Modo', 'requerido' => true, 'unico' => true],
      ],
      'usado_en' => ['distribucion_agenda.modo_id', 'distribucions.modo_id'],
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
    $query = AuxModos::query();

    // Si hay un filtro de nombre, se agrega la condición
    if ($name) {
      $query->where('nombre', 'like', '%' . $name . '%');
    }

    // Aplica la paginación después de construir la consulta
    $modos = $query->orderBy($sortField)->paginate(15);
    // Retorna la vista con los datos
    return view('pages.Tools.Modo.index', compact('modos', 'name'));
  }

}
