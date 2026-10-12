<?php

namespace App\Http\Controllers;

use App\Models\Fichas;
use Illuminate\Http\Request;

/**
 * Fichas: datos históricos importados de las planillas por cliente (cuenta corriente).
 */
class FichasController extends Controller
{
  public function index(Request $request)
  {
    $clientes = Fichas::select('origen_tabla')->distinct()->orderByRaw('CAST(origen_tabla AS UNSIGNED), origen_tabla')->get();

    $fichas = Fichas::query()
      ->when($request->filled('origen_tabla'), fn($q) => $q->where('origen_tabla', $request->origen_tabla))
      ->orderByDesc('id')
      ->paginate(15)
      ->withQueryString();

    // Dentro de la página, en orden cronológico
    $fichas->setCollection($fichas->getCollection()->sortBy('id'));

    // Números reales de las planillas: ficha = número de cliente, M = código de molino
    $nombresCliente = \App\Models\ExpedicionCliente::whereNotNull('numero')->pluck('nombre_planilla', 'numero');
    $siglasMolino = \App\Models\ExpedicionMolino::whereNotNull('codigo')->pluck('sigla', 'codigo');

    return view('pages.Expedicion.Fichas.index', compact('fichas', 'clientes', 'nombresCliente', 'siglasMolino'));
  }
}
