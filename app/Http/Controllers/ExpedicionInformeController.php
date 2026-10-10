<?php

namespace App\Http\Controllers;

use App\Models\ExpEntrega;
use App\Models\ExpPedido;
use App\Models\ExpPedidoLinea;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExpedicionInformeController extends Controller
{
  /**
   * Recuento de tolvas y bolsas entregadas por molino, con totales generales.
   */
  public function tolvas(Request $request)
  {
    $request->validate([
      'desde' => 'nullable|date',
      'hasta' => 'nullable|date|after_or_equal:desde',
    ]);

    $desde = Carbon::parse($request->input('desde', now()->startOfMonth()))->toDateString();
    $hasta = Carbon::parse($request->input('hasta', now()))->toDateString();

    $filas = ExpPedidoLinea::query()
      ->join('exp_pedidos as p', 'p.id', '=', 'exp_pedido_lineas.pedido_id')
      ->join('expedicion_molinos as m', 'm.id', '=', 'p.expedicion_molino_id')
      ->where('p.vigente', true)
      ->whereNotIn('p.estado', [ExpPedido::ANULADO, ExpPedido::PENDIENTE_CONFIRMACION])
      ->whereDate('exp_pedido_lineas.fecha_entrega', '>=', $desde)
      ->whereDate('exp_pedido_lineas.fecha_entrega', '<=', $hasta)
      ->groupBy('m.id', 'm.sigla')
      ->orderBy('m.sigla')
      ->selectRaw('m.id, m.sigla, SUM(exp_pedido_lineas.tolvas) as tolvas, SUM(exp_pedido_lineas.bolsas) as bolsas, COUNT(*) as entregas')
      ->get();

    return view('pages.Expedicion.Informes.tolvas', compact('filas', 'desde', 'hasta'));
  }

  /**
   * Entregas descargadas cuyo certificado de calidad todavía no llegó (para reclamar a los molinos).
   */
  public function certificados()
  {
    $entregas = ExpEntrega::with(['linea.pedido.cliente', 'linea.pedido.molino'])
      ->where('descarga_completa', true)
      ->where('certificado_recibido', false)
      ->get()
      ->sortBy(fn($e) => [$e->linea->pedido->molino->sigla, $e->linea->fecha_entrega])
      ->groupBy(fn($e) => $e->linea->pedido->molino->sigla);

    return view('pages.Expedicion.Informes.certificados', compact('entregas'));
  }
}
