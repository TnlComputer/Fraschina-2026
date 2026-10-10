<?php

namespace App\Http\Controllers;

use App\Models\AuxVeraz;
use App\Http\Controllers\Concerns\CrudAuxiliar;
use Illuminate\Http\Request;

class VerazController extends Controller
{
  use CrudAuxiliar;

  protected function auxiliar(): array
  {
    return [
      'modelo' => AuxVeraz::class,
      'titulo' => 'Veraz',
      'ruta' => 'veraz',
      'campos' => [
        'estado' => ['label' => 'Estado veraz', 'requerido' => true, 'unico' => true],
        'color' => ['label' => 'Color', 'tipo' => 'color'],
      ],
      'usado_en' => ['distribucion_agenda.veraz_id', 'distribucions.veraz_id'],
    ];
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $name = trim($request->get('name'));
    $sortField = $request->get('sort', 'estado'); // Campo por defecto

    // Comienza la consulta base
    $query = AuxVeraz::query();

    // Si hay un filtro de nombre, se agrega la condición
    if ($name) {
      $query->where('estado', 'like', '%' . $name . '%');
    }

    // Aplica la paginación después de construir la consulta
    $verazes = $query->orderBy($sortField)->paginate(15);

    // Retorna la vista con los datos
    return view('pages.Tools.Veraz.index', compact('verazes', 'name'));
  }

}
