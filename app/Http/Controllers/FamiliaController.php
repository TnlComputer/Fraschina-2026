<?php

namespace App\Http\Controllers;

use App\Models\AuxFamilias;
use App\Http\Controllers\Concerns\CrudAuxiliar;
use Illuminate\Http\Request;

class FamiliaController extends Controller
{
  use CrudAuxiliar;

  protected function auxiliar(): array
  {
    return [
      'modelo' => AuxFamilias::class,
      'titulo' => 'Familia',
      'femenino' => true,
      'ruta' => 'familias',
      'campos' => [
        'familia' => ['label' => 'Familia', 'requerido' => true, 'unico' => true],
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
    $sortField = $request->get('sort', 'familia'); // Campo por defecto

    // Comienza la consulta base
    $query = AuxFamilias::query();

    // Si hay un filtro de nombre, se agrega la condición
    if ($name) {
      $query->where('nomFamilia', 'like', '%' . $name . '%');
    }

    // Aplica la paginación después de construir la consulta
    $familias = $query->orderBy($sortField)->paginate(15);
    // Retorna la vista con los datos
    return view('pages.Tools.Familia.index', compact('familias', 'name'));
  }

}
