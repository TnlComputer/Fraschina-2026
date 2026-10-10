<?php

namespace App\Http\Controllers;

use App\Models\ExpPedidoMovimiento;
use Illuminate\Http\Request;

/**
 * Diaria: registro histórico de cada movimiento de pedidos.
 */
class ExpedicionDiariaController extends Controller
{
  public function index(Request $request)
  {
    $request->validate([
      'desde' => 'nullable|date',
      'hasta' => 'nullable|date',
      'numero' => 'nullable|integer',
    ]);

    $movimientos = ExpPedidoMovimiento::with(['pedido.cliente', 'pedido.molino', 'usuario'])
      ->when($request->filled('desde'), fn($q) => $q->whereDate('created_at', '>=', $request->desde))
      ->when($request->filled('hasta'), fn($q) => $q->whereDate('created_at', '<=', $request->hasta))
      ->when($request->filled('numero'), fn($q) => $q->where('numero', $request->numero))
      ->when($request->filled('accion'), fn($q) => $q->where('accion', $request->accion))
      ->latest('id')
      ->paginate(30)
      ->withQueryString();

    return view('pages.Expedicion.diaria', [
      'movimientos' => $movimientos,
      'acciones' => ExpPedidoMovimiento::ACCIONES,
    ]);
  }
}
