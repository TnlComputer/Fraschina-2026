<?php

namespace App\Http\Controllers;

use App\Models\DistribucionAgenda;
use App\Models\DistribucionNroPedidos;
use App\Models\ExpPedido;
use App\Models\ExpPedidoLinea;
use App\Models\ExpPedidoMovimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Días con datos para marcar en los calendarios de filtro (partials/calendario-dias).
 * Cada fuente respeta el permiso de su sección. Devuelve {"2026-10-12": 3, ...}.
 */
class CalendarioController extends Controller
{
  /** fuente => [permiso, consulta(desde, hasta) que devuelve dia => cantidad] */
  private function fuentes(): array
  {
    $porDia = fn($q, string $columna) => $q->selectRaw("DATE($columna) as dia, COUNT(*) as n")->groupBy('dia')->pluck('n', 'dia');

    return [
      'reparto' => ['permiso_2', fn($d, $h) => $porDia(DistribucionNroPedidos::where('status', 'A')->conLineas()
        ->whereDate('fechaEntrega', '>=', $d)->whereDate('fechaEntrega', '<=', $h), 'fechaEntrega')],
      'agenda' => ['permiso_2', fn($d, $h) => $porDia(DistribucionAgenda::where('status', 'A')
        ->whereDate('fecha', '>=', $d)->whereDate('fecha', '<=', $h), 'fecha')],
      'exp_pedidos' => ['permiso_7', fn($d, $h) => $porDia(ExpPedido::vigentes()
        ->whereDate('fecha_pedido', '>=', $d)->whereDate('fecha_pedido', '<=', $h), 'fecha_pedido')],
      'exp_entregas' => ['permiso_7', fn($d, $h) => $porDia(ExpPedidoLinea::whereHas('pedido', fn($q) => $q->vigentes()->where('estado', '!=', ExpPedido::ANULADO))
        ->whereDate('fecha_entrega', '>=', $d)->whereDate('fecha_entrega', '<=', $h), 'fecha_entrega')],
      'exp_diaria' => ['permiso_7', fn($d, $h) => $porDia(ExpPedidoMovimiento::query()
        ->whereDate('created_at', '>=', $d)->whereDate('created_at', '<=', $h), 'created_at')],
      'hist_diaria' => ['permiso_7', fn($d, $h) => $porDia(DB::table('exp_historial_diaria')
        ->whereDate('fecha', '>=', $d)->whereDate('fecha', '<=', $h), 'fecha')],
      'hist_control' => ['permiso_7', fn($d, $h) => $porDia(DB::table('exp_historial_control')
        ->whereDate('fecha', '>=', $d)->whereDate('fecha', '<=', $h), 'fecha')],
      'auditoria' => ['permiso_99', fn($d, $h) => $porDia(DB::table('auditoria')
        ->where('created_at', '>=', "$d 00:00:00")->where('created_at', '<=', "$h 23:59:59"), 'created_at')],
    ];
  }

  public function dias(Request $request)
  {
    $datos = $request->validate([
      'fuente' => 'required|string',
      'desde' => 'required|date_format:Y-m-d',
      'hasta' => 'required|date_format:Y-m-d|after_or_equal:desde',
    ]);
    $fuente = $this->fuentes()[$datos['fuente']] ?? abort(404);
    abort_unless($request->user()->can($fuente[0]), 403);

    return response()->json($fuente[1]($datos['desde'], $datos['hasta']));
  }
}
