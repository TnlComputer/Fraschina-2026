<?php

namespace App\Http\Controllers;

use App\Models\Agro;
use App\Models\AuxBarrios;
use Illuminate\Http\Request;
use App\Models\AuxMunicipios;
use App\Models\AuxLocalidades;
use Illuminate\Support\Facades\DB;

class AgroController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $name = trim($request->get('name', ''));

    // Construir la consulta base
    $query = DB::table('agros as resp')
      ->join('auxbarrios as auxb', 'resp.barrio_id', '=', 'auxb.id')
      ->join('auxlocalidades as auxLoc', 'resp.localidad_id', '=', 'auxLoc.id')
      ->join('auxmunicipios as auxMun', 'resp.municipio_id', '=', 'auxMun.id')
      ->join('auxrubros as auxrub', 'resp.rubro_id', '=', 'auxrub.id')
      ->select(
        'auxb.nombrebarrio as barrio',
        'auxLoc.localidad as localidad',
        'auxMun.ciudadmunicipio as municipio',
        'auxrub.nombre as rubro',
        'resp.razonsocial',
        'resp.dire_calle',
        'resp.dire_nro',
        'resp.piso',
        'resp.codpost',
        'resp.dire_obs',
        'resp.telefono',
        'resp.fax',
        'resp.cuit',
        'resp.correo',
        'resp.dpto',
        'resp.marcas',
        'resp.info',
        'resp.id',
        'resp.correo'
      )
      ->where('resp.status', '=', 'A'); // Filtrar por status = 'A'

    // Filtrar por nombre si se proporciona
    if ($name) {
      $query->where(function ($q) use ($name) {
        $q->where('razonsocial', 'like', '%' . $name . '%');
        // ->orWhere('marcas', 'like', '%' . $name . '%'); // Opcional
      });
    }

    // Ordenar por razonsocial
    $query->orderBy('razonsocial', 'asc');

    // Paginar los resultados
    $agros = $query->paginate(15);

    return view('pages.Agro.index', compact('agros', 'name'));
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    $barrios = tabla_aux('auxbarrios')->orderBy('nombrebarrio', 'asc')->get();
    $localidades = tabla_aux('auxlocalidades')->orderBy('localidad', 'asc')->get();
    $municipios = tabla_aux('auxmunicipios')->orderBy('ciudadmunicipio', 'asc')->get();
    $rubros = tabla_aux('auxrubros')->orderBy('nombre', 'asc')->get();

    return view('pages.Agro.create', compact('barrios', 'localidades', 'municipios', 'rubros'));
  }


  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'razonsocial' => 'required|max:50',
      'dire_calle' => 'nullable|max:50',
      'dire_nro' => 'nullable|max:30',
      'piso' => 'nullable|max:4',
      'dpto' => 'nullable|max:4',
      'dire_obs' => 'nullable|max:100',
      'codpost' => 'nullable|max:30',
      'barrio_id' => 'nullable|exists:auxbarrios,id',
      'municipio_id' => 'nullable|exists:auxmunicipios,id',
      'localidad_id' => 'nullable|exists:auxlocalidades,id',
      'telefono' => 'nullable|max:200',
      'fax' => 'nullable|max:50',
      'cuit' => 'nullable|max:50',
      'correo' => 'nullable|email|max:255',
      'marcas' => 'nullable|max:255',
      'rubro_id' => 'nullable|exists:auxrubros,id',
      'info' => 'nullable',
    ]);

    Agro::create($validated);

    return redirect()->route('agro.index')->with('success', 'Agro creado exitosamente.');
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit($id)
  {
    $agro = Agro::findOrFail($id);

    $barrios = tabla_aux('auxbarrios', $agro->barrio_id)->orderBy('nombrebarrio', 'asc')->get();
    $localidades = tabla_aux('auxlocalidades', $agro->localidad_id)->orderBy('localidad', 'asc')->get();
    $municipios = tabla_aux('auxmunicipios', $agro->municipio_id)->orderBy('ciudadmunicipio', 'asc')->get();
    $rubros = tabla_aux('auxrubros', $agro->rubro_id)->orderBy('nombre', 'asc')->get();

    return view('pages.Agro.edit', compact('agro', 'barrios', 'localidades', 'municipios', 'rubros'));
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, $id)
  {
    $validated = $request->validate([
      'razonsocial' => 'required|max:50',
      'dire_calle' => 'nullable|max:50',
      'dire_nro' => 'nullable|max:30',
      'piso' => 'nullable|max:4',
      'dpto' => 'nullable|max:4',
      'dire_obs' => 'nullable|max:100',
      'codpost' => 'nullable|max:30',
      'barrio_id' => 'nullable|exists:auxbarrios,id',
      'municipio_id' => 'nullable|exists:auxmunicipios,id',
      'localidad_id' => 'nullable|exists:auxlocalidades,id',
      'telefono' => 'nullable|max:200',
      'fax' => 'nullable|max:50',
      'cuit' => 'nullable|max:50',
      'correo' => 'nullable|email|max:255',
      'marcas' => 'nullable|max:255',
      'rubro_id' => 'nullable|exists:auxrubros,id',
      'info' => 'nullable',
    ]);

    $agro = Agro::findOrFail($id);
    $agro->update($validated);

    return redirect()->route('agro.index')->with('success', 'Agro actualizado exitosamente.');
  }

  /**
   * Display the specified resource.
   */
  public function show($id)
  {
    try {
      $agro = DB::table('agros as agr')
        ->join('auxbarrios as auxb', 'agr.barrio_id', '=', 'auxb.id')
        ->join('auxlocalidades as auxloc', 'agr.localidad_id', '=', 'auxloc.id')
        ->join('auxmunicipios as auxmun', 'agr.municipio_id', '=', 'auxmun.id')
        ->join('auxrubros as auxrub', 'agr.rubro_id', '=', 'auxrub.id')
        ->select(
          'agr.*',
          'auxb.nombrebarrio as barrio',
          'auxloc.localidad as localidad',
          'auxmun.ciudadmunicipio as municipio',
          'auxrub.nombre as rubro',
        )
        ->where('agr.id', $id)
        ->first();

      // dd($id, $agro);

      $personal = DB::table('agro_personal as dp')
        ->leftJoin('auxareas as a', 'dp.area_id', '=', 'a.id')
        ->leftJoin('auxcargos as c', 'dp.cargo_id', '=', 'c.id')
        ->leftJoin('auxprofesiones as pf', 'dp.profesion_id', '=', 'pf.id')
        ->select(
          'dp.*',
          'a.area as area',
          'c.cargo as cargo',
          'pf.nombreprofesion as profesion',
        )
        ->where('dp.agro_id', '=', $id)
        ->where('dp.status', '=', 'A')
        ->get();

      // dd($agro, $personal);

      return view('pages.Agro.show', compact('agro', 'personal'));
    } catch (\Exception $e) {
      report($e);
      // Redirigir en caso de error
      return redirect()->route('agro.index')->with('error', 'Ocurrió un error. Intente nuevamente o contacte al administrador.');
    }
  }

  /**
   * Remove the specified resource from storage.
   * Changes the status from 'A' to 'D' instead of deleting the record.
   */
  public function destroy($id)
  {
    $agro = Agro::findOrFail($id);

    // Cambiar el estado a 'D' para desactivar el registro
    $agro->status = 'D';
    $agro->save();

    return redirect()->route('agro.index')
      ->with('success', 'El registro fue desactivado exitosamente.');
  }
}