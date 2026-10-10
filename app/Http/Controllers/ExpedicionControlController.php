<?php

namespace App\Http\Controllers;

use App\Models\ExpEntrega;
use App\Models\ExpPedido;
use App\Models\ExpPedidoLinea;
use App\Services\CentralPedidos;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Control de entregas (reemplaza la hoja "Ctrol."): entregas del día y del siguiente,
 * con transportista, horarios, kilos, remito, descarga y certificado.
 */
class ExpedicionControlController extends Controller
{
  public function index(Request $request)
  {
    $request->validate(['fecha' => 'nullable|date']);

    $fecha = Carbon::parse($request->input('fecha', now()))->startOfDay();
    $siguiente = $fecha->copy()->addDay();

    $lineas = ExpPedidoLinea::with(['pedido.cliente', 'pedido.molino', 'entrega'])
      ->where(fn($q) => $q->whereDate('fecha_entrega', $fecha->toDateString())->orWhereDate('fecha_entrega', $siguiente->toDateString()))
      ->whereHas('pedido', fn($q) => $q->vigentes()->whereNotIn('estado', [ExpPedido::ANULADO, ExpPedido::PENDIENTE_CONFIRMACION]))
      ->orderBy('fecha_entrega')
      ->orderBy('hora')
      ->get()
      ->groupBy(fn($l) => $l->fecha_entrega->toDateString());

    return view('pages.Expedicion.control', [
      'fecha' => $fecha,
      'siguiente' => $siguiente,
      'lineas' => $lineas,
    ]);
  }

  public function update(Request $request, ExpPedidoLinea $linea, CentralPedidos $central)
  {
    $validated = $request->validate([
      'transportista' => 'nullable|string|max:100',
      'chofer' => 'nullable|string|max:100',
      'fecha_carga' => 'nullable|date',
      'hora_carga' => 'nullable|string|max:20',
      'hora_llegada' => 'nullable|string|max:20',
      'observaciones' => 'nullable|string|max:1000',
      'descarga_completa' => 'nullable|boolean',
      'kg_molino' => 'nullable|integer|min:0',
      'kg_cliente' => 'nullable|integer|min:0',
      'nro_remito' => 'nullable|string|max:50',
      'certificado_recibido' => 'nullable|boolean',
      'fecha' => 'nullable|date',
    ]);

    $validated['descarga_completa'] = $request->boolean('descarga_completa');
    $validated['certificado_recibido'] = $request->boolean('certificado_recibido');

    $entrega = ExpEntrega::firstOrNew(['pedido_linea_id' => $linea->id]);
    if ($validated['certificado_recibido'] && !$entrega->certificado_recibido) {
      $entrega->certificado_fecha = now()->toDateString();
    }
    if (!$validated['certificado_recibido']) {
      $entrega->certificado_fecha = null;
    }
    $entrega->fill(collect($validated)->except('fecha')->all())->save();

    $central->registrar(
      $linea->pedido,
      'entrega',
      "Línea {$linea->linea}: " . collect($entrega->getChanges())->except(['updated_at', 'created_at'])
        ->map(fn($v, $k) => "$k=" . (is_bool($v) ? ($v ? 'sí' : 'no') : $v))->implode(', '),
      auth()->id()
    );

    return redirect()->route('expedicion_control.index', ['fecha' => $request->input('fecha')])
      ->with('success', "Entrega del pedido {$linea->pedido->codigo} actualizada.");
  }
}
