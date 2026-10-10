<?php

namespace App\Http\Controllers;

use App\Models\AuxMunicipios;
use App\Http\Controllers\Concerns\CrudAuxiliar;
use Illuminate\Http\Request;

class MunicipioController extends Controller
{
  use CrudAuxiliar;

  protected function auxiliar(): array
  {
    return [
      'modelo' => AuxMunicipios::class,
      'titulo' => 'Ciudad / municipio',
      'femenino' => true,
      'ruta' => 'ciudades_municipios',
      'campos' => [
        'ciudadmunicipio' => ['label' => 'Ciudad / municipio', 'requerido' => true, 'unico' => true],
        'usos' => ['label' => 'Se usa en', 'tipo' => 'usos'],
      ],
      'usado_en' => ['agros.municipio_id', 'distribucion_agenda.municipio_id', 'distribucions.municipio_id', 'molinos.municipio_id', 'proveedores.municipio_id', 'representacions.municipio_id', 'transportes.municipio_id'],
    ];
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $name = trim($request->get('name'));
    $sortField = $request->get('sort', 'ciudadmunicipio'); // Campo por defecto

    // Comienza la consulta base
    $query = AuxMunicipios::query();

    // Si hay un filtro de nombre, se agrega la condición
    if ($name) {
      $query->where('ciudadmunicipio', 'like', '%' . $name . '%');
    }

    // Aplica la paginación después de construir la consulta
    $municipios = $query->orderBy($sortField)->paginate(15);
    // Retorna la vista con los datos
    return view('pages.Tools.Municipio.index', compact('municipios', 'name'));
  }

}
