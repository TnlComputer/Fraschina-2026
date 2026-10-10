<?php

namespace App\Http\Controllers;

use App\Models\Distribucion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistribucionController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $name = trim($request->get('name'));

    // Consulta base ordenada por razonsocial. LEFT JOIN para no ocultar clientes
    // a los que les falta calle, barrio, localidad, municipio o zona.
    $query = DB::table('distribucions as d')
      ->leftJoin('auxcalles as auxCalle', 'd.dire_calle_id', '=', 'auxCalle.id')
      ->leftJoin('auxbarrios as auxB', 'd.barrio_id', '=', 'auxB.id')
      ->leftJoin('auxlocalidades as auxLoc', 'd.localidad_id', '=', 'auxLoc.id')
      ->leftJoin('auxmunicipios as auxMun', 'd.municipio_id', '=', 'auxMun.id')
      ->leftJoin('auxzonas as auxZon', 'd.zona_id', '=', 'auxZon.id')
      ->select(
        'd.clisg_id',
        'd.razonsocial',
        'd.nomfantasia',
        'd.dire_nro',
        'd.piso',
        'd.codpost',
        'd.dire_obs',
        'd.telefono',
        'd.fax',
        'd.cuit',
        'd.correo',
        'd.dpto',
        'd.marcas',
        'd.info',
        'd.id',
        'auxCalle.calle as dire_calle',
        'auxB.nombrebarrio as barrio',
        'auxLoc.localidad as localidad',
        'auxMun.ciudadmunicipio as municipio',
        'auxZon.nombre as zona'
      )
      ->where('d.status', '=', 'A') // Filtrar por estado activo
      ->orderBy('d.razonsocial', 'asc'); // Ordenar por razonsocial de manera ascendente

    if ($name) {
      $query->where(function ($subQuery) use ($name) {
        $subQuery->orWhere('d.nomfantasia', 'like', '%' . $name . '%')
          ->orWhere('d.razonsocial', 'like', '%' . $name . '%')
          ->orWhere('d.clisg_id', 'like', '%' . $name . '%');
      });
    }

    $distribuciones = $query->paginate(15);

    return view('pages.Distribucion.index', compact('distribuciones', 'name'));
  }

  public function show($id)
  {

    try {
      // Consulta principal para la distribución
      $distribucion = DB::table('distribucions as d')
        ->leftJoin('auxcalles as auxCalle', 'd.dire_calle_id', '=', 'auxCalle.id')
        ->leftJoin('auxbarrios as auxB', 'd.barrio_id', '=', 'auxB.id')
        ->leftJoin('auxlocalidades as auxLoc', 'd.localidad_id', '=', 'auxLoc.id')
        ->leftJoin('auxmunicipios as auxMun', 'd.municipio_id', '=', 'auxMun.id')
        ->leftJoin('auxzonas as auxZon', 'd.zona_id', '=', 'auxZon.id')
        ->leftJoin('auxrubros as auxRub', 'd.rubro_id', '=', 'auxRub.id')
        ->leftJoin('auxtamanio as auxTam', 'd.tamanio_id', '=', 'auxTam.id')
        ->leftJoin('auxmodos as auxMod', 'd.modo_id', '=', 'auxMod.id')
        ->leftJoin('auxcontacto as auxCon', 'd.contacto_id', '=', 'auxCon.id')
        ->leftJoin('auxestados as auxEst', 'd.estado_id', '=', 'auxEst.id')
        ->leftJoin('auxcobrar as auxCob', 'd.cobrar_id', '=', 'auxCob.id')
        ->leftJoin('auxpagos as auxCar', 'd.cobro_id', '=', 'auxCar.id')
        ->leftJoin('auxtipopagos as auxTPg', 'd.tcobro_id', '=', 'auxTPg.id')
        ->leftJoin('auxveraz as auxVer', 'd.veraz_id', '=', 'auxVer.id')
        ->select(
          'd.clisg_id',
          'd.razonsocial',
          'd.nomfantasia',
          'auxCalle.calle as dire_calle',
          'd.dire_nro',
          'd.piso',
          'd.codpost',
          'd.dire_obs',
          'd.telefono',
          'd.fax',
          'd.cuit',
          'd.correo',
          'd.dpto',
          'd.auto',
          'd.marcas',
          'd.info',
          'd.id',
          'd.lunes',
          'd.sabado',
          'd.fac_imp',
          'd.obsrecep',
          'd.desde',
          'd.desde1',
          'd.hasta',
          'd.hasta1',
          'd.productoCDA',
          'auxB.nombrebarrio as barrio',
          'auxLoc.localidad as localidad',
          'auxMun.ciudadmunicipio as municipio',
          'auxZon.nombre as zona',
          'auxRub.nombre as rubro',
          'auxTam.nombre as tamanio',
          'auxMod.nombre as modo',
          'auxCon.contacto as contacto',
          'auxEst.nomEstado as estado',
          'auxVer.estado as veraz',
          'auxCob.accion as cobrar',
          'auxCar.nombre as cobro',
          'auxTPg.nombre as tpago',
        )
        ->where('d.id', '=', $id)
        ->where('d.status', '=', 'A')
        ->first();

      // dd($id, $distribucion);

      if (!$distribucion) {
        return redirect()->route('distribucion.index')->with('error', 'Distribución no encontrada');
      }

      // Consulta para el personal
      $personal = DB::table('distribucion_personal as dp')
        ->leftJoin('auxareas as a', 'dp.area_id', '=', 'a.id')
        ->leftJoin('auxcargos as c', 'dp.cargo_id', '=', 'c.id')
        ->leftJoin('auxprofesiones as pf', 'dp.profesion_id', '=', 'pf.id')
        ->select(
          'dp.nombre',
          'dp.apellido',
          'dp.teldirecto',
          'dp.interno',
          'dp.telcelular',
          'dp.telparticular',
          'dp.email',
          'dp.observaciones',
          'dp.id',
          'dp.fuera',
          'a.area as area',
          'c.cargo as cargo',
          'pf.nombreprofesion as profesion',
        )
        ->where('dp.distribucion_id', '=', $id)
        ->where('dp.status', '=', 'A')
        ->get();

      // Consulta para los productos
      $productos = DB::table('distribucion_productos as dp')
        ->leftJoin('productos_c_d_a as p', 'dp.producto_id', '=', 'p.id')
        ->select('p.productoCDA as nomproducto', 'dp.precio', 'dp.fecha', 'dp.fechaUEnt', 'dp.id')
        ->where('dp.distribucion_id', '=', $id)
        ->where('dp.status', '=', 'A')
        ->get();

      return view('pages.Distribucion.show', compact('distribucion', 'personal', 'productos'));
    } catch (\Exception $e) {
      report($e);
      // Redirigir en caso de error
      return redirect()->route('distribucion.index')->with('error', 'Ocurrió un error. Intente nuevamente o contacte al administrador.');
    }
  }

  public function create()
  {
    // Obtener datos auxiliares ordenados
    $calles = DB::table('auxcalles')->orderBy('calle')->get();
    $barrios = DB::table('auxbarrios')->orderBy('nombrebarrio')->get();
    $localidades = DB::table('auxlocalidades')->orderBy('localidad')->get();
    $municipios = DB::table('auxmunicipios')->orderBy('ciudadmunicipio')->get();
    $zonas = DB::table('auxzonas')->orderBy('nombre')->get();
    $rubros = DB::table('auxrubros')
      ->where('distribuciones', 'SI')
      ->orderBy('nombre')
      ->get();

    $tamanos = DB::table('auxtamanio')->orderBy('nombre')->get();
    $modos = DB::table('auxmodos')->orderBy('nombre')->get();
    $contactos = DB::table('auxcontacto')->orderBy('contacto')->get();
    $estados = DB::table('auxestados')->orderBy('nomEstado')->get();
    $verazs = DB::table('auxveraz')->orderBy('estado')->get();
    $cobrars = DB::table('auxcobrar')->orderBy('accion')->get();
    $pagos = DB::table('auxpagos')->orderBy('nombre')->get();
    $tiposPago = DB::table('auxtipopagos')->orderBy('nombre')->get();

    $distribucion = new Distribucion();
    // Pasar datos a la vista
    return view('pages.Distribucion.create', compact(
      'distribucion',
      'calles',
      'barrios',
      'localidades',
      'municipios',
      'zonas',
      'rubros',
      'contactos',
      'estados',
      'verazs',
      'cobrars',
      'pagos',
      'tiposPago',
      'tamanos',
      'modos'
    ));
  }

  public function store(Request $request)
  {
    // Validación de datos
    $validated = $request->validate($this->reglas(), $this->mensajes());

    // dd($validated['auto']);

    // Validación adicional para horas
    if ($request->input('desde') && $request->input('hasta') && $request->input('desde') > $request->input('hasta')) {
      return redirect()->back()->withErrors(['desde' => 'La hora de inicio no puede ser mayor que la hora de fin.']);
    }


    // Normalización de checkboxes
    $validated['auto'] = $request->has('auto') ? 'SI' : 'NO';
    $validated['lunes'] = $request->has('lunes') ? 'SI' : 'NO';
    $validated['sabado'] = $request->has('sabado') ? 'SI' : 'NO';
    $validated['fac_imp'] = $request->has('fac_imp') ? 'SI' : 'NO';
    $validated['status'] = 'A';

    // Crear la distribución
    Distribucion::create($validated);

    $name = $request->razonsocial ? $request->razonsocial : $request->nomfantasia;

    // Redirigir con mensaje de éxito
    return redirect()->route('distribucion.index', ['name' => $name])->with('success', 'Distribución almacenada con éxito');
  }


  public function edit($id)
  {
    $distribucion = DB::table('distribucions')->where('id', $id)->where('status', 'A')->first();
    abort_if(!$distribucion, 404);

    // $calles = DB::table('auxcalles')->pluck('calle', 'id');
    // $barrios = DB::table('auxbarrios')->pluck('nombrebarrio', 'id');
    // $localidades = DB::table('auxlocalidades')->pluck('localidad', 'id');
    // $municipios = DB::table('auxmunicipios')->pluck('ciudadmunicipio', 'id');
    // $zonas = DB::table('auxzonas')->pluck('nombre', 'id');

    $calles = DB::table('auxcalles')->orderBy('calle')->get();
    $barrios = DB::table('auxbarrios')->orderBy('nombrebarrio')->get();
    $localidades = DB::table('auxlocalidades')->orderBy('localidad')->get();
    $municipios = DB::table('auxmunicipios')->orderBy('ciudadmunicipio')->get();
    $zonas = DB::table('auxzonas')->orderBy('nombre')->get();
    $rubros = DB::table('auxrubros')
      ->where('distribuciones', 'SI')
      ->orderBy('nombre')
      ->get();

    // Consultas a tablas auxiliares
    // $horas = DB::table('auxhoras')->orderBy('hora')->get();
    $tamanos = DB::table('auxtamanio')->orderBy('nombre')->get();
    $modos = DB::table('auxmodos')->orderBy('nombre')->get();
    $contactos = DB::table('auxcontacto')->orderBy('contacto')->get();
    $estados = DB::table('auxestados')->orderBy('nomEstado')->get();
    $verazs = DB::table('auxveraz')->orderBy('estado')->get();
    $cobrars = DB::table('auxcobrar')->orderBy('accion')->get();
    $pagos = DB::table('auxpagos')->orderBy('nombre')->get();
    $tiposPago = DB::table('auxtipopagos')->orderBy('nombre')->get();

    return view('pages.Distribucion.edit', compact(
      'distribucion',
      'calles',
      'barrios',
      'localidades',
      'municipios',
      'zonas',
      'rubros',
      'contactos',
      'estados',
      'verazs',
      'cobrars',
      'pagos',
      'tiposPago',
      'tamanos',
      'modos',
    ));
  }
  public function update(Request $request, $id)
  {
    $validated = $request->validate($this->reglas(), $this->mensajes());

    // dd($validated['auto']);

    // Validación adicional para horas
    if ($request->input('desde') && $request->input('hasta') && $request->input('desde') > $request->input('hasta')) {
      return redirect()->back()->withErrors(['desde' => 'La hora de inicio no puede ser mayor que la hora de fin.']);
    }


    // Normalización de checkboxes
    $validated['auto'] = $request->has('auto') ? 'SI' : 'NO';
    $validated['lunes'] = $request->has('lunes') ? 'SI' : 'NO';
    $validated['sabado'] = $request->has('sabado') ? 'SI' : 'NO';
    $validated['fac_imp'] = $request->has('fac_imp') ? 'SI' : 'NO';

    // Actualización del registro (los desactivados no se editan ni se reactivan por esta vía)
    $distribucion = Distribucion::where('status', 'A')->findOrFail($id);
    $distribucion->update($validated);

    $name = $request->razonsocial ? $request->razonsocial : $request->nomfantasia;
    // dd($name);  // Imprime el valor de $name
    return redirect()->route('distribucion.index', ['name' => $name])->with('success', 'Distribución actualizada exitosamente.');
  }

  public function destroy($id)
  {
    // Buscar el registro por ID
    $distribucion = Distribucion::findOrFail($id);

    // Actualizar el campo 'status' a 'D'
    $distribucion->update(['status' => 'D']);

    // Redirigir al índice con un mensaje de éxito
    return redirect()->route('distribucion.index')->with('success', 'Distribución desactivada exitosamente.');
  }

  /**
   * Reglas de validación compartidas por store() y update().
   */
  private function reglas(): array
  {
    return [
      'clisg_id' => 'nullable|integer',
      'razonsocial' => 'required|string|max:50',
      'nomfantasia' => 'nullable|string|max:100',
      'dire_calle_id' => 'required|integer|exists:auxcalles,id',
      'dire_nro' => 'nullable|string|max:30',
      'piso' => 'nullable|string|max:10',
      'dpto' => 'nullable|string|max:10',
      'codpost' => 'nullable|string|max:30',
      'dire_obs' => 'nullable|string|max:100',
      'barrio_id' => 'required|integer|exists:auxbarrios,id',
      'municipio_id' => 'required|integer|exists:auxmunicipios,id',
      'localidad_id' => 'required|integer|exists:auxlocalidades,id',
      'zona_id' => 'required|integer|exists:auxzonas,id',
      'telefono' => 'nullable|string|max:20',
      'cuit' => 'nullable|string|max:20',
      'correo' => 'nullable|string|max:255|email',
      'marcas' => 'nullable|string|max:255',
      'info' => 'nullable|string',
      'rubro_id' => 'required|integer|exists:auxrubros,id',
      'tamanio_id' => 'required|integer|exists:auxtamanio,id',
      'modo_id' => 'required|integer|exists:auxmodos,id',
      'contacto_id' => 'required|integer|exists:auxcontacto,id',
      'auto' => 'nullable|in:on,off',
      'veraz_id' => 'required|integer|exists:auxveraz,id',
      'estado_id' => 'required|integer|exists:auxestados,id',
      'productoCDA' => 'nullable|string',
      'desde' => 'nullable|date_format:H:i',
      'hasta' => 'nullable|date_format:H:i',
      'desde1' => 'nullable|date_format:H:i',
      'hasta1' => 'nullable|date_format:H:i',
      'lunes' => 'nullable|in:on,off',
      'sabado' => 'nullable|in:on,off',
      'fac_imp' => 'nullable|in:on,off',
      'obsrecep' => 'nullable|string|max:255',
      'cobrar_id' => 'nullable|integer|exists:auxcobrar,id',
      'cobro_id' => 'nullable|integer|exists:auxpagos,id',
      'tcobro_id' => 'nullable|integer|exists:auxtipopagos,id',
    ];
  }

  private function mensajes(): array
  {
    return [
      'desde.date_format' => 'El campo "desde" debe estar en el formato HH:mm.',
      'hasta.date_format' => 'El campo "hasta" debe estar en el formato HH:mm.',
      'desde1.date_format' => 'El campo "desde1" debe estar en el formato HH:mm.',
      'hasta1.date_format' => 'El campo "hasta1" debe estar en el formato HH:mm.',
      'tamanio_id' => 'El campo "tamaño" es obligatorio',
    ];
  }
}
