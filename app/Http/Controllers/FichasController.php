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
    $clientes = Fichas::select('origen_tabla')->distinct()->orderBy('origen_tabla')->get();

    $fichas = Fichas::query()
      ->when($request->filled('origen_tabla'), fn($q) => $q->where('origen_tabla', $request->origen_tabla))
      ->orderByDesc('id')
      ->paginate(15)
      ->withQueryString();

    // Dentro de la página, en orden cronológico
    $fichas->setCollection($fichas->getCollection()->sortBy('id'));

    return view('pages.Expedicion.Fichas.index', compact('fichas', 'clientes'));
  }
}
