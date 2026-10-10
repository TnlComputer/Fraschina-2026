<?php

namespace App\Http\Controllers;

use App\Models\AuxZonas;
use App\Http\Controllers\Concerns\CrudAuxiliar;
use Illuminate\Http\Request;

class ZonaController extends Controller
{
  use CrudAuxiliar;

  protected function auxiliar(): array
  {
    return [
      'modelo' => AuxZonas::class,
      'titulo' => 'Zona',
      'femenino' => true,
      'ruta' => 'zonas',
      'campos' => [
        'nombre' => ['label' => 'Zona', 'requerido' => true, 'unico' => true],
        'usos' => ['label' => 'Se usa en', 'tipo' => 'usos'],
      ],
      'usado_en' => ['distribucion_agenda.zona_id', 'distribucions.zona_id', 'representacions.zona_id'],
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
    $query = AuxZonas::query();

    // Si hay un filtro de nombre, se agrega la condición
    if ($name) {
      $query->where('nombre', 'like', '%' . $name . '%');
    }

    // Aplica la paginación después de construir la consulta
    $zonas = $query->orderBy($sortField)->paginate(15);

    // Retorna la vista con los datos
    return view('pages.Tools.Zona.index', compact('zonas', 'name'));
  }

}
