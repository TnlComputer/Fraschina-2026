<?php

namespace App\Http\Controllers;

use App\Models\Deposito;
use App\Services\Geocodificador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Tools → Depósitos. El principal es el punto de salida del reparto; la baja es lógica.
 */
class DepositoController extends Controller
{
  public function __construct(private Geocodificador $geocodificador)
  {
  }

  public function index()
  {
    $depositos = Deposito::orderByDesc('principal')->orderBy('nombre')->get();
    return view('pages.Tools.Deposito.index', compact('depositos'));
  }

  public function create()
  {
    // El primero que se carga queda como principal
    return view('pages.Tools.Deposito.form', ['deposito' => new Deposito(['principal' => !Deposito::exists()])]);
  }

  public function store(Request $request)
  {
    $deposito = new Deposito();
    $aviso = $this->guardar($request, $deposito);
    return redirect()->route('depositos.index')->with('success', 'Depósito creado correctamente.' . $aviso);
  }

  public function show($id)
  {
    return redirect()->route('depositos.edit', $id);
  }

  public function edit($id)
  {
    return view('pages.Tools.Deposito.form', ['deposito' => Deposito::findOrFail($id)]);
  }

  public function update(Request $request, $id)
  {
    $aviso = $this->guardar($request, Deposito::findOrFail($id));
    return redirect()->route('depositos.index')->with('success', 'Depósito actualizado correctamente.' . $aviso);
  }

  public function destroy($id)
  {
    $deposito = Deposito::findOrFail($id);
    if ($deposito->principal && Deposito::whereKeyNot($deposito->id)->exists()) {
      return back()->with('error', 'Es el depósito principal: primero marque otro como principal.');
    }
    $deposito->delete();
    return redirect()->route('depositos.index')->with('success', 'Depósito eliminado.');
  }

  /** Valida y guarda; si no hay ubicación (o cambió la dirección) la busca en el mapa. Devuelve un aviso si no la encontró. */
  private function guardar(Request $request, Deposito $deposito): string
  {
    // Decimales con coma, como se escriben en Argentina (-34,638); también se acepta el punto
    foreach (['latitud', 'longitud'] as $campo) {
      if ($request->filled($campo)) {
        $request->merge([$campo => str_replace(',', '.', trim($request->input($campo)))]);
      }
    }
    $datos = $request->validate([
      'nombre' => ['required', 'string', 'max:100', Rule::unique('depositos', 'nombre')->ignore($deposito->id)->whereNull('deleted_at')],
      'direccion' => 'required|string|max:150',
      'localidad' => 'required|string|max:150',
      'latitud' => 'nullable|numeric|between:-90,90|required_with:longitud',
      'longitud' => 'nullable|numeric|between:-180,180|required_with:latitud',
      'observaciones' => 'nullable|string|max:1000',
    ], [], ['direccion' => 'dirección']);
    $datos['principal'] = $request->boolean('principal') || !Deposito::whereKeyNot($deposito->id)->exists();

    $cambioDireccion = $deposito->exists && ($deposito->direccion !== $datos['direccion'] || $deposito->localidad !== $datos['localidad']);
    $ubicacionAMano = $request->filled('latitud') && (!$cambioDireccion
      || (float) $datos['latitud'] !== (float) $deposito->latitud || (float) $datos['longitud'] !== (float) $deposito->longitud);
    $aviso = '';
    if (!$ubicacionAMano) {
      $ubicacion = $this->geocodificador->ubicar($datos['direccion'], $datos['localidad']);
      $datos['latitud'] = $ubicacion['latitud'] ?? null;
      $datos['longitud'] = $ubicacion['longitud'] ?? null;
      $aviso = $ubicacion ? '' : ' No se encontró la dirección en el mapa: cargue la latitud y la longitud a mano.';
    }

    DB::transaction(function () use ($deposito, $datos) {
      if ($datos['principal']) {
        Deposito::whereKeyNot($deposito->id)->where('principal', true)->get()->each->update(['principal' => false]);
      }
      $deposito->fill($datos)->save();
    });

    return $aviso;
  }
}
