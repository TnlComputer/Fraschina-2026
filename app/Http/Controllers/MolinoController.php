<?php

namespace App\Http\Controllers;

use App\Models\Molino;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MolinoController extends Controller
{
  public function index(Request $request)
  {
    $name = trim($request->get('name'));

    $molinos = DB::table('molinos as resp')
      ->join('auxbarrios as auxb', 'resp.barrio_id', '=', 'auxb.id')
      ->join('auxlocalidades as auxLoc', 'resp.localidad_id', '=', 'auxLoc.id')
      ->join('auxmunicipios as auxMun', 'resp.municipio_id', '=', 'auxMun.id')
      ->select(
        'auxb.nombrebarrio as barrio',
        'auxLoc.localidad as localidad',
        'auxMun.ciudadmunicipio as municipio',
        'resp.*'
      )
      ->where('resp.status', '=', 'A')
      ->when($name, function ($query, $name) {
        return $query->where('resp.razonsocial', 'like', '%' . $name . '%');
      })
      ->orderBy('resp.razonsocial', 'ASC')
      ->paginate(15);

    return view('pages.Molino.index', compact('molinos', 'name'));
  }


  public function create()
  {
    $barrios = DB::table('auxbarrios')->orderBy('nombrebarrio', 'asc')->get();
    $localidades = DB::table('auxlocalidades')->orderBy('localidad', 'asc')->get();
    $municipios = DB::table('auxmunicipios')->orderBy('ciudadmunicipio', 'asc')->get();

    return view('pages.Molino.create', compact('barrios', 'localidades', 'municipios'));
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

    (new Molino())->forceFill($validated)->save();

    return redirect()->route('molino.index')->with('success', 'Molino creado exitosamente.');
  }

  public function edit($id)
  {
    $molino = DB::table('molinos')->where('id', $id)->first();

    $barrios = DB::table('auxbarrios')->orderBy('nombrebarrio', 'asc')->get();
    $localidades = DB::table('auxlocalidades')->orderBy('localidad', 'asc')->get();
    $municipios = DB::table('auxmunicipios')->orderBy('ciudadmunicipio', 'asc')->get();

    return view('pages.Molino.edit', compact('molino', 'barrios', 'localidades', 'municipios'));
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

    Molino::findOrFail($id)->forceFill($validated)->save();

    return redirect()->route('molino.index')->with('success', 'Molino actualizado exitosamente.');
  }

  public function show($id)
  {
    try {

      $molino = DB::table('molinos as mol')
        ->join('auxbarrios as auxb', 'mol.barrio_id', '=', 'auxb.id')
        ->join('auxlocalidades as auxloc', 'mol.localidad_id', '=', 'auxloc.id')
        ->join('auxmunicipios as auxmun', 'mol.municipio_id', '=', 'auxmun.id')
        ->select(
          'mol.*',
          'auxb.nombrebarrio as barrio',
          'auxloc.localidad as localidad',
          'auxmun.ciudadmunicipio as municipio',
        )
        ->where('mol.id', $id)
        ->first();


      $personal = DB::table('molino_personal as dp')
        ->leftJoin('auxareas as a', 'dp.area_id', '=', 'a.id')
        ->leftJoin('auxcargos as c', 'dp.cargo_id', '=', 'c.id')
        ->leftJoin('auxprofesiones as pf', 'dp.profesion_id', '=', 'pf.id')
        ->select(
          'dp.*',
          'a.area as area',
          'c.cargo as cargo',
          'pf.nombreprofesion as profesion',
        )
        ->where('dp.molino_id', '=', $id)
        ->where('dp.status', '=', 'A')
        ->get();

      // dd($molino, $personal);

      return view('pages.Molino.show', compact('molino', 'personal'));
    } catch (\Exception $e) {
      report($e);
      // Redirigir en caso de error
      return redirect()->route('molino.index')->with('error', 'Ocurrió un error. Intente nuevamente o contacte al administrador.');
    }
  }


  public function destroy($id)
  {
    Molino::findOrFail($id)->forceFill(['status' => 'D'])->save();

    return redirect()->route('molino.index')->with('success', 'Molino eliminado exitosamente.');
  }
}