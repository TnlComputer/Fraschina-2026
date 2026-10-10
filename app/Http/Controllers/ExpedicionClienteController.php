<?php

namespace App\Http\Controllers;

use App\Models\ExpedicionCliente;
use App\Models\Representacion;
use Illuminate\Http\Request;

/**
 * Clientes de Expedición: sigla, vínculo con el cliente (Representación) y textos de la planilla.
 */
class ExpedicionClienteController extends Controller
{
  public function index(Request $request)
  {
    $name = trim((string) $request->get('name'));

    $clientes = ExpedicionCliente::with('representacion:id,razonsocial')
      ->when($name, fn($q) => $q->where(function ($q) use ($name) {
        $q->where('sigla', 'like', "%{$name}%")
          ->orWhereHas('representacion', fn($r) => $r->where('razonsocial', 'like', "%{$name}%"));
      }))
      ->orderBy('sigla')
      ->paginate(20)
      ->withQueryString();

    return view('pages.Expedicion.Cliente.index', compact('clientes', 'name'));
  }

  public function create()
  {
    return view('pages.Expedicion.Cliente.form', [
      'cliente' => new ExpedicionCliente(),
      'representaciones' => $this->representaciones(),
    ]);
  }

  public function store(Request $request)
  {
    ExpedicionCliente::create($this->validar($request));

    return redirect()->route('expedicion_cliente.index')->with('success', 'Cliente creado.');
  }

  public function show(string $id)
  {
    return redirect()->route('expedicion_cliente.edit', $id);
  }

  public function edit(ExpedicionCliente $expedicion_cliente)
  {
    return view('pages.Expedicion.Cliente.form', [
      'cliente' => $expedicion_cliente,
      'representaciones' => $this->representaciones(),
    ]);
  }

  public function update(Request $request, ExpedicionCliente $expedicion_cliente)
  {
    $expedicion_cliente->update($this->validar($request));

    return redirect()->route('expedicion_cliente.index')->with('success', 'Cliente actualizado.');
  }

  public function destroy(ExpedicionCliente $expedicion_cliente)
  {
    // Borrado lógico: queda en la base y en sus pedidos, pero no se muestra ni se puede elegir
    $expedicion_cliente->delete();

    return redirect()->route('expedicion_cliente.index')->with('success', 'Cliente eliminado.');
  }

  private function representaciones()
  {
    return Representacion::where('status', 'A')->orderBy('razonsocial')->get(['id', 'razonsocial']);
  }

  private function validar(Request $request): array
  {
    return $request->validate([
      'sigla' => 'required|string|max:5',
      'representacion_id' => 'nullable|exists:representacions,id',
      'linea1' => 'nullable|string|max:200',
      'linea2' => 'nullable|string|max:200',
      'linea3' => 'nullable|string|max:200',
      'linea4' => 'nullable|string|max:200',
      'linea5' => 'nullable|string|max:200',
      'linea6' => 'nullable|string|max:200',
      'obs' => 'nullable|string',
      'comentarios' => 'nullable|string',
    ]);
  }
}
