<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\DistribucionAgenda;
use App\Models\DistribucionNroPedidos;
use App\Models\ExpEntrega;
use App\Models\ExpPedido;
use App\Models\ExpPedidoLinea;

/**
 * Pantalla de inicio: resumen del día según los permisos del usuario.
 */
class DashboardController extends Controller
{
  public function __invoke()
  {
    $usuario = auth()->user();
    $hoy = now()->toDateString();
    $datos = ['usuario' => $usuario];

    if ($usuario->can('permiso_2')) {
      $pedidos = fn() => DistribucionNroPedidos::where('status', 'A')->conLineas();

      // Próximo día de reparto con pedidos (hoy o el siguiente)
      $proximo = $pedidos()->whereDate('fechaEntrega', '>=', $hoy)->min('fechaEntrega');
      $delProximo = $proximo ? $pedidos()->whereDate('fechaEntrega', $proximo) : null;

      $datos['distribucion'] = [
        'proximo' => $proximo,
        'pedidos' => $delProximo ? (clone $delProximo)->count() : 0,
        'sinFactura' => $delProximo ? (clone $delProximo)->whereIn('tipo', ['P', 'PT'])->where(fn($q) => $q->whereNull('nroFactura')->orWhere('nroFactura', ''))->count() : 0,
        'sinChofer' => $delProximo ? (clone $delProximo)->where(fn($q) => $q->whereNull('chofer')->orWhere('chofer', ''))->count() : 0,
        'total' => $delProximo ? (float) (clone $delProximo)->sum('totalPedido') : 0,
        'agendaHoy' => DistribucionAgenda::where('status', 'A')->whereDate('fecha', $hoy)->count(),
        'ultimosPedidos' => $pedidos()->with('distribucion:id,nomfantasia')->latest('id')->limit(6)->get(),
      ];
    }

    if ($usuario->can('permiso_7')) {
      $datos['expedicion'] = [
        'pendientes' => ExpPedido::vigentes()->where('estado', ExpPedido::PENDIENTE_CONFIRMACION)->count(),
        'sinEnviar' => ExpPedido::vigentes()->where('estado', ExpPedido::CONFIRMADO)->whereNull('enviado_at')->count(),
        'entregasHoy' => ExpPedidoLinea::whereDate('fecha_entrega', $hoy)
          ->whereHas('pedido', fn($q) => $q->vigentes()->whereNotIn('estado', [ExpPedido::ANULADO, ExpPedido::PENDIENTE_CONFIRMACION]))
          ->count(),
        'certificados' => ExpEntrega::whereHas('linea')->where('descarga_completa', true)->where('certificado_recibido', false)->count(),
      ];
    }

    if ($usuario->can('permiso_99')) {
      $datos['auditoria'] = Auditoria::latest('id')->limit(8)->get();
    }

    return view('dashboard', $datos);
  }
}
