<?php

namespace App\Http\Controllers;

use App\Models\AgendaGral;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AgendaGralController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $name = trim($request->get('name'));

    $query = DB::table('agendas as ag')
      ->join('auxprofesiones as auxProf', 'ag.cod_prof', '=', 'auxProf.id')
      ->select(
        'ag.nombre',
        'ag.apellido',
        'ag.empresa_institucion',
        'ag.tel_particular',
        'ag.tel_laboral',
        'ag.interno',
        'ag.celular',
        'ag.mail',
        'ag.direccion',
        'ag.observaciones',
        'ag.id',
        'auxProf.nombreprofesion as profesion_especialidad_oficio'
      )
      ->where('status', '!=', false);

    if ($name) {
      $query->where(function ($query) use ($name) {
        $query->where('ag.nombre', 'like', '%' . $name . '%')
          ->orWhere('ag.apellido', 'like', '%' . $name . '%')
          ->orWhere('ag.empresa_institucion', 'like', '%' . $name . '%');
      });
    }

    $agendaGral = $query->paginate(10);

    return view('pages.AgendaGral.index', compact('agendaGral', 'name'));
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    $profesiones = DB::table('auxprofesiones as auxProf')
      ->where('auxProf.agendas', '=', 'SI')
      ->orderBy('nombreprofesion', 'asc')
      ->get();
    return view('pages.AgendaGral.create', ['profesiones' => $profesiones]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    // Validar los datos de entrada
    $data = $request->validate([
      'Nombre' => 'required|string|max:255',
      'Apellido' => 'required|string|max:255',
      'Empresa_Institucion' => 'nullable|string|max:255',
      'cod_prof' => 'nullable|integer|exists:auxprofesiones,id',
      'Tel_Particular' => 'nullable|string|max:20',
      'Tel_Laboral' => 'nullable|string|max:20',
      'Interno' => 'nullable|string|max:10',
      'Celular' => 'nullable|string|max:20',
      'Mail' => 'nullable|email|max:255',
      'Direccion' => 'nullable|string|max:255',
      'Observaciones' => 'nullable|string',
    ]);

    // Crear el nuevo registro con el status en true
    (new AgendaGral())->forceFill([
      'nombre' => $request->input('Nombre'),
      'apellido' => $request->input('Apellido'),
      'empresa_institucion' => $request->input('Empresa_Institucion'),
      'cod_prof' => $request->input('cod_prof'),
      'tel_particular' => $request->input('Tel_Particular'),
      'tel_laboral' => $request->input('Tel_Laboral'),
      'interno' => $request->input('Interno'),
      'celular' => $request->input('Celular'),
      'mail' => $request->input('Mail'),
      'direccion' => $request->input('Direccion'),
      'observaciones' => $request->input('Observaciones'),
      'status' => true, // Establecer status como true
      'created_at' => now(),
      'updated_at' => now(),
    ])->save();

    // $name = $data['Nombre'];

    // // Redireccionar con mensaje de éxito
    // // return redirect()->route('AgendaGral.index')->with('success', 'Registro creado exitosamente.');
    // return redirect()->route('AgendaGral.index', compact('name'))
    //   ->with('success', 'Registro actualizado con éxito.');
    return redirect()->route('AgendaGral.index', ['name' => $data['Nombre']])
      ->with('success', 'Registro actualizado con éxito.');
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit($id)
  {
    $agenda = DB::table('agendas')->where('id', $id)->first();
    $profesiones = DB::table('auxprofesiones')->get();

    if (!$agenda) {
      return redirect()->route('AgendaGral.index')->with('error', 'Registro no encontrado.');
    }

    return view('pages.AgendaGral.edit', compact('agenda', 'profesiones'));
  }

  /**
   * Update the specified resource in storage.
   */

  public function update(Request $request, $id)
  {
    // Validar los datos
    $data = $request->validate([
      'Nombre' => 'required|string|max:255',
      'Apellido' => 'required|string|max:255',
      'Empresa_Institucion' => 'nullable|string|max:255',
      'cod_prof' => 'nullable|integer|exists:auxprofesiones,id',
      'Tel_Particular' => 'nullable|string|max:20',
      'Tel_Laboral' => 'nullable|string|max:20',
      'Interno' => 'nullable|string|max:10',
      'Celular' => 'nullable|string|max:20',
      'Mail' => 'nullable|email|max:255',
      'Direccion' => 'nullable|string|max:255',
      'Observaciones' => 'nullable|string',
    ]);

    // Actualizar el registro
    AgendaGral::findOrFail($id)->forceFill([
      'nombre' => $request->input('Nombre'),
      'apellido' => $request->input('Apellido'),
      'empresa_institucion' => $request->input('Empresa_Institucion'),
      'cod_prof' => $request->input('cod_prof'),
      'tel_particular' => $request->input('Tel_Particular'),
      'tel_laboral' => $request->input('Tel_Laboral'),
      'interno' => $request->input('Interno'),
      'celular' => $request->input('Celular'),
      'mail' => $request->input('Mail'),
      'direccion' => $request->input('Direccion'),
      'observaciones' => $request->input('Observaciones'),
      'updated_at' => now(),
    ])->save();

    return redirect()->route('AgendaGral.index', ['name' => $data['Nombre']])
      ->with('success', 'Registro actualizado con éxito.');

    // return redirect()->route('AgendaGral.index')->with('success', 'Registro actualizado exitosamente.');
  }


  /**
   * Remove the specified resource from storage.
   */
  public function destroy($id)
  {
    $agenda = AgendaGral::findOrFail($id);
    $agenda->status = false;
    $agenda->update();
    return redirect()->route('AgendaGral.index')
      ->with('danger', 'Contacto Eliminado');
  }
}