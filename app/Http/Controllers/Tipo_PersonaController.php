<?php

namespace App\Http\Controllers;

use App\Models\AuxTipoPersonal;
use App\Http\Controllers\Concerns\CrudAuxiliar;
use Illuminate\Http\Request;

class Tipo_PersonaController extends Controller
{
  use CrudAuxiliar;

  protected function auxiliar(): array
  {
    return [
      'modelo' => AuxTipoPersonal::class,
      'titulo' => 'Tipo de persona',
      'femenino' => true,
      'ruta' => 'tipo_persona',
      'campos' => [
        'tipo' => ['label' => 'Tipo de persona', 'requerido' => true, 'unico' => true],
      ],
      'usado_en' => ['distribucion_agenda.tipoper_id'],
    ];
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $name = trim($request->get('name'));
    $sortField = $request->get('sort', 'tipo'); // Campo por defecto

    // Comienza la consulta base
    $query = AuxTipoPersonal::query();

    // Si hay un filtro de nombre, se agrega la condición
    if ($name) {
      $query->where('tipo', 'like', '%' . $name . '%');
    }

    // Aplica la paginación después de construir la consulta
    $tipo_personas = $query->orderBy($sortField)->paginate(15);

    // Retorna la vista con los datos
    return view('pages.Tools.Tipo_Persona.index', compact('tipo_personas', 'name'));
  }

}
