<?php

namespace App\Http\Controllers;

use App\Models\AuxPagos;
use App\Models\AuxVeraz;
use App\Models\AuxZonas;
use App\Models\AuxCalles;
use App\Models\AuxCobrar;
use App\Models\AuxBarrios;
use App\Models\AuxContacto;
use App\Models\AuxTipoPagos;
use App\Models\Representacion;
use Illuminate\Http\Request;
use App\Models\AuxMunicipios;
use App\Models\AuxLocalidades;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RepresentacionController extends Controller
{
  /**
   * Display a listing of the resource.
   */

  public function index(Request $request)
  {
    $name = trim($request->get('name'));

    // Iniciar la consulta con las uniones necesarias
    $query = DB::table('representacions as r')
      ->join('auxbarrios as auxb', 'r.barrio_id', '=', 'auxb.id')
      ->join('auxlocalidades as auxLoc', 'r.localidad_id', '=', 'auxLoc.id')
      ->join('auxmunicipios as auxMun', 'r.municipio_id', '=', 'auxMun.id')
      ->join('auxzonas as auxZon', 'r.zona_id', '=', 'auxZon.id')
      ->select(
        'auxb.nombrebarrio as barrio',
        'auxLoc.localidad as localidad',
        'auxMun.ciudadmunicipio as municipio',
        'auxZon.nombre as zona',
        'r.razonsocial',
        'r.dire_calle',
        'r.dire_nro',
        'r.piso',
        'r.codpost',
        'r.dire_obs',
        'r.telefono',
        'r.fax',
        'r.cuit',
        'r.correo',
        'r.dpto',
        'r.marcas',
        'r.info',
        'r.status',
        'r.id'
      );

    // Aplicar filtro por nombre
    if ($name) {
      $query->where(function ($q) use ($name) {
        $q->where('r.razonsocial', 'like', '%' . $name . '%')
          ->orWhere('r.marcas', 'like', '%' . $name . '%');
      });
    }

    // Filtrar solo las representaciones con status 'A'
    $query->where('r.status', '=', 'A');

    // Ejecutar la consulta con paginación
    $representaciones = $query->paginate(15);

    // Traer datos adicionales para los filtros
    $barrios = AuxBarrios::all();
    $localidades = AuxLocalidades::all();
    $municipios = AuxMunicipios::all();
    $zonas = AuxZonas::all();

    // Pasar los datos a la vista
    return view('pages.Representacion.index', [
      'representaciones' => $representaciones,
      'barrios' => $barrios,
      'localidades' => $localidades,
      'municipios' => $municipios,
      'zonas' => $zonas,
      'name' => $name,
      'paso' => $name ? 'Buscando' : 'NO'
    ]);
  }


  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    $barrios = AuxBarrios::all();
    $localidades = AuxLocalidades::all();
    $municipios = AuxMunicipios::all();
    $zonas = AuxZonas::all();
    // dd($municipios);
    return view('pages.Representacion.create', ['barrios' => $barrios, 'localidades' => $localidades, 'municipios' => $municipios, 'zonas' => $zonas]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $request->validate([
      'razonsocial' => 'required|string|max:255',
      'dire_calle' => 'nullable|string|max:255',
      'dire_nro' => 'nullable|string|max:255',
      'piso' => 'nullable|string|max:255',
      'dpto' => 'nullable|string|max:255',
      'codpost' => 'nullable|string|max:255',
      'dire_obs' => 'nullable|string|max:255',
      'barrio_id' => 'nullable|exists:auxbarrios,id',
      'localidad_id' => 'nullable|exists:auxlocalidades,id',
      'zona_id' => 'nullable|exists:auxzonas,id',
      'municipio_id' => 'nullable|exists:auxmunicipios,id',
      'telefono' => 'nullable|string|max:255',
      'fax' => 'nullable|string|max:255',
      'correo' => 'nullable|string|max:255',
      'cuit' => 'nullable|string|max:255',
      'marcas' => 'nullable|string|max:255',
      'info' => 'nullable|string',
    ]);

    $representacion = new Representacion();
    $representacion->razonsocial = $request->razonsocial;
    $representacion->dire_calle = $request->dire_calle;
    $representacion->dire_nro = $request->dire_nro;
    $representacion->piso = $request->piso;
    $representacion->dpto = $request->dpto;
    $representacion->codpost = $request->codpost;
    $representacion->dire_obs = $request->dire_obs;
    $representacion->barrio_id = $request->barrio_id;
    $representacion->localidad_id = $request->localidad_id;
    $representacion->zona_id = $request->zona_id;
    $representacion->municipio_id = $request->municipio_id;
    $representacion->telefono = $request->telefono;
    $representacion->fax = $request->fax;
    $representacion->correo = $request->correo;
    $representacion->cuit = $request->cuit;
    $representacion->marcas = $request->marcas;
    $representacion->info = $request->info;

    $representacion->save();

    return redirect()->route('representacion.index')->with('success', 'Representación creada correctamente.');
  }

  /**
   * Display the specified resource.
   */
  public function show(Representacion $representacion)
  {
    // Cargar relaciones necesarias con filtro en 'personal' para excluir registros con 'status' igual a 'D'
    $representacion = Representacion::with([
      'personal' => function ($query) {
        $query->where('status', '!=', 'D'); // Filtrar solo registros activos
      },
      'personal.area',          // Carga el área del personal
      'personal.cargo',         // Carga el cargo del personal
      'personal.profesion',     // Carga la profesión del personal
      'zona',                   // Carga la zona de la representación
      'municipio',              // Carga el municipio
      'localidad',              // Carga la localidad
      'barrio',                 // Carga el barrio
      'productos' => function ($query) {
        $query->where('status', '!=', 'D'); // Filtrar solo productos activos
      },
    ])->findOrFail($representacion->id);

    return view('pages.Representacion.show', compact('representacion'));
  }

  /**
   * Show the form for editing the specified resource.
   */
  /**
   * Mostrar la vista de edición de una representación.
   *
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    $representacion = Representacion::findOrFail($id);
    $barrios = AuxBarrios::all(); // Asegúrate de tener estos datos disponibles
    $localidades = AuxLocalidades::all();
    $municipios = AuxMunicipios::all();
    $zonas = AuxZonas::all();

    return view('pages.Representacion.edit', compact('representacion', 'barrios', 'localidades', 'municipios', 'zonas'));
  }

  /**
   * Actualizar una representación en la base de datos.
   *
   * @param \Illuminate\Http\Request $request
   * @param int $id
   * @return \Illuminate\Http\Response
   */

  public function update(Request $request, $id)
  {
    $request->validate([
      'razonsocial' => 'required|string|max:255',
      'dire_calle' => 'nullable|string|max:255',
      'dire_nro' => 'nullable|string|max:255',
      'piso' => 'nullable|string|max:255',
      'dpto' => 'nullable|string|max:255',
      'codpost' => 'nullable|string|max:255',
      'dire_obs' => 'nullable|string|max:255',
      'barrio_id' => 'nullable|exists:auxbarrios,id',
      'localidad_id' => 'nullable|exists:auxlocalidades,id',
      'zona_id' => 'nullable|exists:auxzonas,id',
      'municipio_id' => 'nullable|exists:auxmunicipios,id',
      'telefono' => 'nullable|string|max:255',
      'fax' => 'nullable|string|max:255',
      'correo' => 'nullable|string|max:255',
      'cuit' => 'nullable|string|max:255',
      'marcas' => 'nullable|string|max:255',
      'info' => 'nullable|string',
    ]);

    $representacion = Representacion::findOrFail($id);
    $representacion->razonsocial = $request->razonsocial;
    $representacion->dire_calle = $request->dire_calle;
    $representacion->dire_nro = $request->dire_nro;
    $representacion->piso = $request->piso;
    $representacion->dpto = $request->dpto;
    $representacion->codpost = $request->codpost;
    $representacion->dire_obs = $request->dire_obs;
    $representacion->barrio_id = $request->barrio_id;
    $representacion->localidad_id = $request->localidad_id;
    $representacion->zona_id = $request->zona_id;
    $representacion->municipio_id = $request->municipio_id; // Se actualiza el municipio
    $representacion->telefono = $request->telefono;
    $representacion->fax = $request->fax;
    $representacion->correo = $request->correo;
    $representacion->cuit = $request->cuit;
    $representacion->marcas = $request->marcas;
    $representacion->info = $request->info;

    $representacion->save();

    return redirect()->route('representacion.index')->with('success', 'Representación actualizada correctamente.');
  }


  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Representacion $representacion)
  {
    $representacion->status = $representacion->status === 'A' ? 'D' : 'A';
    $representacion->save();

    // Mensaje según el nuevo estado
    $message = $representacion->status === 'A' ? 'Representación activada correctamente' : 'Representación desactivada correctamente';

    return redirect()->route('representacion.index')->with('success', $message);
  }
}
