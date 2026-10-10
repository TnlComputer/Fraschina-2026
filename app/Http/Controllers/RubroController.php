<?php

namespace App\Http\Controllers;

use App\Models\AuxRubros;
use App\Http\Controllers\Concerns\CrudAuxiliar;
use Illuminate\Http\Request;

class RubroController extends Controller
{
  use CrudAuxiliar;

  protected function auxiliar(): array
  {
    return [
      'modelo' => AuxRubros::class,
      'titulo' => 'Rubro',
      'ruta' => 'rubros',
      'campos' => [
        'nombre' => ['label' => 'Rubro', 'requerido' => true, 'unico' => true],
        'usos' => ['label' => 'Se usa en', 'tipo' => 'usos'],
      ],
      'usado_en' => ['agros.rubro_id', 'distribucion_agenda.rubro_id', 'distribucions.rubro_id', 'proveedores.rubro_id'],
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
    $query = AuxRubros::query();

    // Si hay un filtro de nombre, se agrega la condición
    if ($name) {
      $query->where('nombre', 'like', '%' . $name . '%');
    }

    // Aplica la paginación después de construir la consulta
    $rubros = $query->orderBy($sortField)->paginate(15);
    // Retorna la vista con los datos
    return view('pages.Tools.Rubro.index', compact('rubros', 'name'));
  }

}
