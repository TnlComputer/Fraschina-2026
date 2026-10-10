<?php

namespace App\Http\Controllers;

use App\Models\ExpedicionCliente;
use App\Models\ExpedicionMolino;
use App\Models\ExpedicionMolinoTextoCliente;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Configuración por combinación cliente-molino: plazo y forma de pago habituales,
 * condición, planta, checklist de la planilla y textos propios.
 */
class ExpedicionMolinoTextoClienteController extends Controller
{
  public function index(Request $request)
  {
    $configuraciones = ExpedicionMolinoTextoCliente::with(['cliente.representacion', 'molino.molino'])
      ->when($request->filled('cliente_id'), fn($q) => $q->where('expClie_id', $request->cliente_id))
      ->when($request->filled('molino_id'), fn($q) => $q->where('expMolino_id', $request->molino_id))
      ->orderBy('expClie_id')
      ->orderBy('expMolino_id')
      ->paginate(25)
      ->withQueryString();

    return view('pages.Expedicion.MTC.index', array_merge(compact('configuraciones'), $this->listas()));
  }

  public function create()
  {
    return view('pages.Expedicion.MTC.form', array_merge(
      ['config' => new ExpedicionMolinoTextoCliente(['mostrar_precio' => null, 'mostrar_fecha_alternativa' => true, 'mostrar_especiales' => true])],
      $this->listas()
    ));
  }

  public function store(Request $request)
  {
    ExpedicionMolinoTextoCliente::create($this->validar($request));

    return redirect()->route('expedicion_MTC.index')->with('success', 'Configuración guardada.');
  }

  public function show(string $id)
  {
    return redirect()->route('expedicion_MTC.edit', $id);
  }

  public function edit(ExpedicionMolinoTextoCliente $expedicion_MTC)
  {
    return view('pages.Expedicion.MTC.form', array_merge(['config' => $expedicion_MTC], $this->listas()));
  }

  public function update(Request $request, ExpedicionMolinoTextoCliente $expedicion_MTC)
  {
    $expedicion_MTC->update($this->validar($request, $expedicion_MTC->id));

    return redirect()->route('expedicion_MTC.index')->with('success', 'Configuración actualizada.');
  }

  public function destroy(ExpedicionMolinoTextoCliente $expedicion_MTC)
  {
    $expedicion_MTC->delete();

    return redirect()->route('expedicion_MTC.index')->with('success', 'Configuración eliminada.');
  }

  private function listas(): array
  {
    return [
      'clientes' => ExpedicionCliente::with('representacion:id,razonsocial')->orderBy('sigla')->get(),
      'molinos' => ExpedicionMolino::with('molino:id,razonsocial')->orderBy('sigla')->get(),
    ];
  }

  private function validar(Request $request, ?int $ignorarId = null): array
  {
    $validated = $request->validate([
      'expClie_id' => 'required|exists:expedicion_clientes,id',
      'expMolino_id' => [
        'required',
        'exists:expedicion_molinos,id',
        Rule::unique('expedicion_molinos_textos_clientes')
          ->where(fn($q) => $q->where('expClie_id', $request->expClie_id))
          ->ignore($ignorarId),
      ],
      'plazo_dias' => 'nullable|integer|min:0|max:365',
      'forma_pago' => 'nullable|string|max:100',
      'condicion' => 'nullable|string|max:100',
      'planta' => 'nullable|string|max:100',
      'linea1' => 'nullable|string|max:200',
      'linea2' => 'nullable|string|max:200',
      'linea3' => 'nullable|string|max:200',
      'linea4' => 'nullable|string|max:200',
      'linea5' => 'nullable|string|max:200',
      'linea6' => 'nullable|string|max:200',
    ], [
      'expMolino_id.unique' => 'Ya existe una configuración para ese cliente y molino.',
    ]);

    // mostrar_precio: '' = según el molino, '1' = sí, '0' = no
    $validated['mostrar_precio'] = $request->input('mostrar_precio') === null || $request->input('mostrar_precio') === ''
      ? null
      : $request->boolean('mostrar_precio');

    foreach (['subrayar_precio', 'mostrar_fecha_alternativa', 'mostrar_especiales'] as $check) {
      $validated[$check] = $request->boolean($check);
    }

    return $validated;
  }
}
