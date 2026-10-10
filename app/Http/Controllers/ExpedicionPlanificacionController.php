<?php

namespace App\Http\Controllers;

use App\Models\ExpPedido;
use App\Models\ExpPedidoLinea;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Planificación semanal (reemplaza "Planif sem"): cada entrega en su día, como un almanaque.
 * Las fechas tentativas y los pedidos pendientes de confirmación o de cierre se marcan en azul.
 */
class ExpedicionPlanificacionController extends Controller
{
  public function index(Request $request)
  {
    $request->validate(['semana' => 'nullable|date']);

    $inicio = Carbon::parse($request->input('semana', now()))->startOfWeek(Carbon::MONDAY);
    $fin = $inicio->copy()->addDays(6);

    $lineas = ExpPedidoLinea::with(['pedido.cliente', 'pedido.molino'])
      ->whereDate('fecha_entrega', '>=', $inicio->toDateString())
      ->whereDate('fecha_entrega', '<=', $fin->toDateString())
      ->whereHas('pedido', fn($q) => $q->vigentes()->where('estado', '!=', ExpPedido::ANULADO))
      ->when($request->filled('molino_id'), fn($q) => $q->whereHas('pedido', fn($p) => $p->where('expedicion_molino_id', $request->molino_id)))
      ->orderBy('hora')
      ->get();

    $dias = collect(range(0, 6))->map(fn($i) => $inicio->copy()->addDays($i));
    $porDia = $lineas->groupBy(fn($l) => $l->fecha_entrega->toDateString());

    // Líneas sin fecha (a confirmar) de pedidos activos
    $sinFecha = ExpPedidoLinea::with(['pedido.cliente', 'pedido.molino'])
      ->whereNull('fecha_entrega')
      ->whereHas('pedido', fn($q) => $q->vigentes()->whereNotIn('estado', [ExpPedido::ANULADO, ExpPedido::CERRADO]))
      ->get();

    return view('pages.Expedicion.planificacion', [
      'inicio' => $inicio,
      'dias' => $dias,
      'porDia' => $porDia,
      'sinFecha' => $sinFecha,
      'molinos' => \App\Models\ExpedicionMolino::orderBy('sigla')->get(),
    ]);
  }
}
