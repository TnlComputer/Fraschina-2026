<?php

namespace App\Http\Controllers;

use App\Models\ExpEntrega;
use App\Models\ExpPedido;
use App\Models\ExpPedidoLinea;

/**
 * Tablero de la Central de Pedidos: lo que hay que tener presente hoy.
 */
class ExpedicionController extends Controller
{
  public function index()
  {
    $hoy = now()->toDateString();
    $manana = now()->addDay()->toDateString();

    $pedidos = fn(string $estado) => ExpPedido::with(['cliente.representacion', 'molino.molino', 'lineas'])
      ->vigentes()
      ->where('estado', $estado)
      ->orderBy('fecha_pedido')
      ->get();

    $entregas = fn(string $fecha) => ExpPedidoLinea::with(['pedido.cliente', 'pedido.molino', 'entrega'])
      ->whereDate('fecha_entrega', $fecha)
      ->whereHas('pedido', fn($q) => $q->vigentes()->whereNotIn('estado', [ExpPedido::ANULADO, ExpPedido::PENDIENTE_CONFIRMACION]))
      ->orderBy('hora')
      ->get();

    return view('pages.Expedicion.tablero', [
      'pendientesConfirmacion' => $pedidos(ExpPedido::PENDIENTE_CONFIRMACION),
      'pendientesCierre' => $pedidos(ExpPedido::PENDIENTE_CIERRE),
      'sinEnviar' => ExpPedido::with(['cliente', 'molino'])->vigentes()
        ->where('estado', ExpPedido::CONFIRMADO)->whereNull('enviado_at')->orderBy('numero')->get(),
      'entregasHoy' => $entregas($hoy),
      'entregasManana' => $entregas($manana),
      'certificadosPendientes' => ExpEntrega::where('descarga_completa', true)->where('certificado_recibido', false)->count(),
      'tentativas' => ExpPedidoLinea::where('fecha_tentativa', true)
        ->whereHas('pedido', fn($q) => $q->vigentes()->whereNotIn('estado', [ExpPedido::ANULADO, ExpPedido::CERRADO]))
        ->count(),
    ]);
  }
}
