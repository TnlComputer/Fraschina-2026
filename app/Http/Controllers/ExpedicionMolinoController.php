<?php

namespace App\Http\Controllers;

use App\Models\ExpedicionMolino;
use App\Models\Molino;
use Illuminate\Http\Request;

/**
 * Molinos de Expedición: sigla, vínculo con el molino, contacto y si la planilla muestra precio.
 */
class ExpedicionMolinoController extends Controller
{
  public function index(Request $request)
  {
    $name = trim((string) $request->get('name'));

    $expedicion_molinos = ExpedicionMolino::with('molino:id,razonsocial')
      ->when($name, fn($q) => $q->where(function ($q) use ($name) {
        $q->where('sigla', 'like', "%{$name}%")
          ->orWhereHas('molino', fn($m) => $m->where('razonsocial', 'like', "%{$name}%"));
      }))
      ->orderBy('sigla')
      ->paginate(20)
      ->withQueryString();

    return view('pages.Expedicion.Molino.index', compact('expedicion_molinos', 'name'));
  }

  public function create()
  {
    return view('pages.Expedicion.Molino.form', ['molino' => new ExpedicionMolino(), 'molinos' => $this->molinos()]);
  }

  public function store(Request $request)
  {
    ExpedicionMolino::create($this->validar($request));

    return redirect()->route('expedicion_molino.index')->with('success', 'Molino creado.');
  }

  public function show(string $id)
  {
    return redirect()->route('expedicion_molino.edit', $id);
  }

  public function edit(ExpedicionMolino $expedicion_molino)
  {
    return view('pages.Expedicion.Molino.form', ['molino' => $expedicion_molino, 'molinos' => $this->molinos()]);
  }

  public function update(Request $request, ExpedicionMolino $expedicion_molino)
  {
    $expedicion_molino->update($this->validar($request));

    return redirect()->route('expedicion_molino.index')->with('success', 'Molino actualizado.');
  }

  public function destroy(ExpedicionMolino $expedicion_molino)
  {
    if ($expedicion_molino->pedidos()->exists()) {
      return back()->with('error', 'El molino tiene pedidos: no se puede eliminar.');
    }
    $expedicion_molino->delete();

    return redirect()->route('expedicion_molino.index')->with('success', 'Molino eliminado.');
  }

  private function molinos()
  {
    return Molino::where('status', 'A')->orderBy('razonsocial')->get(['id', 'razonsocial']);
  }

  private function validar(Request $request): array
  {
    $validated = $request->validate([
      'sigla' => 'required|string|max:5',
      'nroMolino_id' => 'nullable|exists:molinos,id',
      'contMolino' => 'nullable|string|max:255',
    ]);
    // calificacion = 1: la planilla muestra el precio (criterio del sistema anterior)
    $validated['calificacion'] = $request->boolean('calificacion') ? '1' : '0';

    return $validated;
  }
}
