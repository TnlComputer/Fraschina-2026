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
use Illuminate\Http\Request;
use App\Models\AuxMunicipios;
use App\Models\AuxLocalidades;
use App\Models\Proveedor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProveedorController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $name = trim($request->get('name', ''));

    // Construir la consulta base
    $query = DB::table('proveedores as resp')
      ->join('auxbarrios as auxb', 'resp.barrio_id', '=', 'auxb.id')
      ->join('auxlocalidades as auxLoc', 'resp.localidad_id', '=', 'auxLoc.id')
      ->join('auxmunicipios as auxMun', 'resp.municipio_id', '=', 'auxMun.id')
      ->join('auxrubros as auxrub', 'resp.rubro_id', '=', 'auxrub.id')
      ->select(
        'auxb.nombrebarrio as barrio',
        'auxLoc.localidad as localidad',
        'auxMun.ciudadmunicipio as municipio',
        'auxrub.nombre as rubro',
        'resp.*'
      )
      ->where('resp.status', '=', 'A'); // Filtrar por status = 'A'

    // Filtrar por nombre si se proporciona
    if ($name) {
      $query->where(function ($q) use ($name) {
        $q->where('razonsocial', 'like', '%' . $name . '%');
      });
    }

    // Ordenar por razonsocial
    $query->orderBy('razonsocial', 'asc');

    // Paginar los resultados
    $proveedores = $query->paginate(15);

    return view('pages.Proveedor.index', compact('proveedores', 'name'));
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

    return view('pages.Proveedor.create', compact('barrios', 'localidades', 'municipios', 'rubros'));
  }


  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'razonsocial' => 'required|max:50',
      'dire_calle' => 'required|max:50',
      'dire_nro' => 'required|max:30',
      'piso' => 'nullable|max:4',
      'dpto' => 'nullable|max:4',
      'dire_obs' => 'nullable|max:100',
      'codpost' => 'nullable|max:30',
      'barrio_id' => 'required|exists:auxbarrios,id',
      'municipio_id' => 'required|exists:auxmunicipios,id',
      'localidad_id' => 'required|exists:auxlocalidades,id',
      'telefono' => 'nullable|max:200',
      'fax' => 'nullable|max:50',
      'cuit' => 'nullable|max:50',
      'correo' => 'nullable|email|max:255',
      'marcas' => 'nullable|max:255',
      'rubro_id' => 'required|exists:auxrubros,id',
      'info' => 'nullable',
    ]);

    $validated['status'] = 'A';

    Proveedor::create($validated);

    return redirect()->route('proveedor.index')->with('success', 'Proveedor creado exitosamente.');
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit($id)
  {
    $proveedor = Proveedor::findOrFail($id);

    $barrios = tabla_aux('auxbarrios', $proveedor->barrio_id)->orderBy('nombrebarrio', 'asc')->get();
    $localidades = tabla_aux('auxlocalidades', $proveedor->localidad_id)->orderBy('localidad', 'asc')->get();
    $municipios = tabla_aux('auxmunicipios', $proveedor->municipio_id)->orderBy('ciudadmunicipio', 'asc')->get();
    $rubros = tabla_aux('auxrubros', $proveedor->rubro_id)->orderBy('nombre', 'asc')->get();

    return view('pages.Proveedor.edit', compact('proveedor', 'barrios', 'localidades', 'municipios', 'rubros'));
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

    $proveedor = Proveedor::findOrFail($id);
    $proveedor->update($validated);

    return redirect()->route('proveedor.index')->with('success', 'Proveedor actualizado exitosamente.');
  }

  /**
   * Display the specified resource.
   */
  public function show($id)
  {
    try {
      // Obtener el proveedor con relaciones
      $proveedor = Proveedor::with([
        'barrio:id,nombrebarrio',           // Relación con barrio
        'localidad:id,localidad',           // Relación con localidad
        'municipio:id,ciudadmunicipio',     // Relación con municipio
        'rubro:id,nombre',                  // Relación con rubro
        'personal.area:id,area',            // Relación con area
        'personal.cargo:id,cargo',          // Relación con cargo
        'personal.profesion:id,nombreprofesion', // Relación con profesion
        'productos.producto:id,nombre', // Relación con producto
      ])->findOrFail($id);

      // dd($proveedor);

      // Pasamos los datos a la vista
      return view('pages.Proveedor.show', compact('proveedor'));
    } catch (\Exception $e) {
      report($e);
      // Redirigir en caso de error
      return redirect()->route('proveedor.index')->with('error', 'Ocurrió un error. Intente nuevamente o contacte al administrador.');
    }
  }


  /**
   * Remove the specified resource from storage.
   * Changes the status from 'A' to 'D' instead of deleting the record.
   */
  public function destroy($id)
  {
    $proveedor = Proveedor::findOrFail($id);

    // Cambiar el estado a 'D' para desactivar el registro
    $proveedor->status = 'D';
    $proveedor->save();

    return redirect()->route('proveedor.index')
      ->with('success', 'El registro fue desactivado exitosamente.');
  }
}
