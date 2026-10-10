<?php

namespace App\Http\Controllers;

use App\Models\AuxLocalidades;
use App\Http\Controllers\Concerns\CrudAuxiliar;
use Illuminate\Http\Request;

class LocalidadController extends Controller
{
  use CrudAuxiliar;

  protected function auxiliar(): array
  {
    return [
      'modelo' => AuxLocalidades::class,
      'titulo' => 'Localidad',
      'femenino' => true,
      'ruta' => 'localidades',
      'campos' => [
        'localidad' => ['label' => 'Localidad', 'requerido' => true, 'unico' => true],
        'usos' => ['label' => 'Se usa en', 'tipo' => 'usos'],
      ],
      'usado_en' => ['agros.localidad_id', 'distribucion_agenda.localidad_id', 'distribucions.localidad_id', 'molinos.localidad_id', 'proveedores.localidad_id', 'representacions.localidad_id', 'transportes.localidad_id'],
    ];
  }

    /**
     * Display a listing of the resource.
     */
  public function index(Request $request)
  {
    $name = trim($request->get('name'));
    $sortField = $request->get('sort', 'localidad'); // Campo por defecto

    // Comienza la consulta base
    $query = AuxLocalidades::query();

    // Si hay un filtro de nombre, se agrega la condición
    if ($name) {
      $query->where('localidad', 'like', '%' . $name . '%');
    }

    // Aplica la paginación después de construir la consulta
    $localidades = $query->orderBy($sortField)->paginate(15);
    
    // Retorna la vista con los datos
    return view('pages.Tools.Localidad.index', compact('localidades', 'name'));
  }

}
