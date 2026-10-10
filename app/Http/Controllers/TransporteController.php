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
use App\Models\Transporte;
use Illuminate\Http\Request;
use App\Models\AuxMunicipios;
use App\Models\AuxLocalidades;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransporteController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $name = trim($request->get('name'));

    // Construir la consulta base
    $query = DB::table('transportes as resp')
      ->join('auxbarrios as auxb', 'resp.barrio_id', '=', 'auxb.id')
      ->join('auxlocalidades as auxLoc', 'resp.localidad_id', '=', 'auxLoc.id')
      ->join('auxmunicipios as auxMun', 'resp.municipio_id', '=', 'auxMun.id')
      ->select(
        'auxb.nombrebarrio as barrio',
        'auxLoc.localidad as localidad',
        'auxMun.ciudadmunicipio as municipio',
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
      ->where('status', '=', 'A');

    // Agregar filtro por nombre si está presente
    if (!empty($name)) {
      $query->where(function ($q) use ($name) {
        $q->where('razonsocial', 'like', '%' . $name . '%')
          ->orWhere('marcas', 'like', '%' . $name . '%');
      });
    }

    // Ejecutar la consulta con paginación
    $transportes = $query->paginate(15);

    // Retornar la vista con los datos
    return view('pages.Transporte.index', compact('transportes', 'name'));
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    $barrios = DB::table('auxbarrios')->orderBy('nombrebarrio', 'asc')->get();
    $localidades = DB::table('auxlocalidades')->orderBy('localidad', 'asc')->get();
    $municipios = DB::table('auxmunicipios')->orderBy('ciudadmunicipio', 'asc')->get();

    return view('pages.Transporte.create', compact('barrios', 'localidades', 'municipios'));
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'razonsocial'   => 'required|string|max:50',
      'dire_calle'    => 'nullable|string|max:50',
      'dire_nro'      => 'nullable|string|max:30',
      'piso'          => 'nullable|string|max:4',
      'dpto'          => 'nullable|string|max:4',
      'dire_obs'      => 'nullable|string|max:100',
      'codpost'       => 'nullable|string|max:30',
      'localidad_id'  => 'required|exists:auxlocalidades,id',
      'municipio_id'  => 'required|exists:auxmunicipios,id',
      'barrio_id'     => 'required|exists:auxbarrios,id',
      'telefono'      => 'nullable|string|max:200',
      'fax'           => 'nullable|string|max:50',
      'cuit'          => 'nullable|string|max:50',
      'info'          => 'nullable|string',
      'correo'        => 'nullable|string|email|max:255',
      'marcas'        => 'nullable|string|max:255',
    ]);

    $validated['status'] = 'A';

    (new Transporte())->forceFill($validated)->save();

    return redirect()->route('transporte.index')->with('success', 'Transporte creado exitosamente.');
  }

  public function edit($id)
  {
    $transporte = DB::table('transportes')->where('id', $id)->first();

    $barrios = DB::table('auxbarrios')->orderBy('nombrebarrio', 'asc')->get();
    $localidades = DB::table('auxlocalidades')->orderBy('localidad', 'asc')->get();
    $municipios = DB::table('auxmunicipios')->orderBy('ciudadmunicipio', 'asc')->get();

    return view('pages.Transporte.edit', compact('transporte', 'barrios', 'localidades', 'municipios'));
  }

  public function update(Request $request, $id)
  {
    $validated = $request->validate([
      'razonsocial'   => 'required|string|max:50',
      'dire_calle'    => 'nullable|string|max:50',
      'dire_nro'      => 'nullable|string|max:30',
      'piso'          => 'nullable|string|max:4',
      'dpto'          => 'nullable|string|max:4',
      'dire_obs'      => 'nullable|string|max:100',
      'codpost'       => 'nullable|string|max:30',
      'localidad_id'  => 'required|exists:auxlocalidades,id',
      'municipio_id'  => 'required|exists:auxmunicipios,id',
      'barrio_id'     => 'required|exists:auxbarrios,id',
      'telefono'      => 'nullable|string|max:200',
      'fax'           => 'nullable|string|max:50',
      'cuit'          => 'nullable|string|max:50',
      'info'          => 'nullable|string',
      'correo'        => 'nullable|string|email|max:255',
      'marcas'        => 'nullable|string|max:255',
    ]);

    Transporte::findOrFail($id)->forceFill($validated)->save();

    return redirect()->route('transporte.index', ['name' => $request->razonsocial])->with('success', 'Transporte actualizado exitosamente.');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function show($id)
  {
    try {

      $transporte = DB::table('transportes as resp')
        ->join('auxbarrios as auxb', 'resp.barrio_id', '=', 'auxb.id')
        ->join('auxlocalidades as auxloc', 'resp.localidad_id', '=', 'auxloc.id')
        ->join('auxmunicipios as auxmun', 'resp.municipio_id', '=', 'auxmun.id')
        ->select(
          'resp.*',
          'auxb.nombrebarrio as barrio',
          'auxloc.localidad as localidad',
          'auxmun.ciudadmunicipio as municipio',
        )
        ->where('resp.id', $id)
        ->first();


      $personal = DB::table('transporte_personal as dp')
        ->leftJoin('auxareas as a', 'dp.area_id', '=', 'a.id')
        ->leftJoin('auxcargos as c', 'dp.cargo_id', '=', 'c.id')
        ->leftJoin('auxprofesiones as pf', 'dp.profesion_id', '=', 'pf.id')
        ->select(
          'dp.*',
          'a.area as area',
          'c.cargo as cargo',
          'pf.nombreprofesion as profesion',
        )
        ->where('dp.transporte_id', '=', $id)
        ->where('dp.status', '=', 'A')
        ->get();

      // dd($transporte, $personal);

      return view('pages.Transporte.show', compact('transporte', 'personal'));
    } catch (\Exception $e) {
      report($e);
      // Redirigir en caso de error
      return redirect()->route('transporte.index')->with('error', 'Ocurrió un error. Intente nuevamente o contacte al administrador.');
    }
  }


  public function destroy($id)
  {
    Transporte::findOrFail($id)->forceFill(['status' => 'D'])->save();

    return redirect()->route('transporte.index')->with('success', 'Transporte eliminado exitosamente.');
  }
}
