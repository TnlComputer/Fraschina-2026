<?php

namespace App\Http\Controllers;

use App\Models\AuxContacto;
use App\Http\Controllers\Concerns\CrudAuxiliar;
use Illuminate\Http\Request;

class Contacto_inicialController extends Controller
{
  use CrudAuxiliar;

  protected function auxiliar(): array
  {
    return [
      'modelo' => AuxContacto::class,
      'titulo' => 'Contacto inicial',
      'ruta' => 'contacto_inicial',
      'campos' => [
        'contacto' => ['label' => 'Contacto inicial', 'requerido' => true, 'unico' => true],
      ],
      'usado_en' => ['distribucion_agenda.contacto_id', 'distribucions.contacto_id'],
    ];
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $name = trim($request->get('name'));
    $sortField = $request->get('sort', 'contacto'); // Campo por defecto

    // Comienza la consulta base
    $query = AuxContacto::query();

    // Si hay un filtro de nombre, se agrega la condición
    if ($name) {
      $query->where('contacto', 'like', '%' . $name . '%');
    }

    // Aplica la paginación después de construir la consulta
    $contactos = $query->orderBy($sortField)->paginate(15);
    // Retorna la vista con los datos
    return view('pages.Tools.Contacto_inicial.index', compact('contactos', 'name'));
  }

}
