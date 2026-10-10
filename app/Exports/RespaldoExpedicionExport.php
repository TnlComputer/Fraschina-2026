<?php

namespace App\Exports;

use App\Models\ExpedicionGeneral;
use App\Models\ExpPedido;
use App\Models\ExpPedidoLinea;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Respaldo diario de la Central de Pedidos, para consultar si no hay acceso al sistema:
 * pedidos activos con sus líneas, entregas de los próximos días y calidad reciente.
 */
class RespaldoExpedicionExport implements WithMultipleSheets
{
  public function sheets(): array
  {
    $activos = [ExpPedido::PENDIENTE_CONFIRMACION, ExpPedido::CONFIRMADO, ExpPedido::PENDIENTE_CIERRE];
    $fecha = fn($d) => $d?->format('d/m/Y');

    $lineas = ExpPedidoLinea::with(['pedido.cliente', 'pedido.molino', 'entrega'])
      ->whereHas('pedido', fn($q) => $q->vigentes()->whereIn('estado', $activos))
      ->orderBy('fecha_entrega')
      ->get();

    $pedidos = $lineas->map(fn($l) => [
      $l->pedido->codigo,
      $l->pedido->estado_nombre,
      $l->pedido->tipo_nombre,
      $fecha($l->pedido->fecha_pedido),
      $l->pedido->molino->sigla,
      $l->pedido->cliente->sigla,
      $l->linea,
      $l->calidad,
      $l->tolvas,
      $l->bolsas,
      $l->kilos,
      $l->precio,
      $l->condicion,
      $l->planta,
      $fecha($l->fecha_entrega),
      $l->fecha_tentativa ? 'Sí' : '',
      $l->hora,
      $l->plazo_dias,
      $l->forma_pago,
      $l->lugar_entrega,
      $l->observaciones,
    ]);

    $proximas = $lineas
      ->filter(fn($l) => $l->fecha_entrega && $l->fecha_entrega->between(now()->startOfDay(), now()->addDays(14)))
      ->map(fn($l) => [
        $fecha($l->fecha_entrega),
        $l->hora,
        $l->pedido->codigo,
        $l->pedido->molino->sigla,
        $l->pedido->cliente->sigla,
        $l->calidad,
        $l->envase,
        $l->entrega?->transportista,
        $l->entrega?->chofer,
        $l->entrega?->hora_carga,
        $l->entrega?->kg_molino,
        $l->entrega?->kg_cliente,
        $l->entrega?->nro_remito,
        $l->entrega?->descarga_completa ? 'Sí' : '',
      ])->values();

    $calidad = ExpedicionGeneral::where(fn($q) => $q->where('status', '!=', 'D')->orWhereNull('status'))
      ->whereDate('fecha', '>=', now()->subDays(90)->toDateString())
      ->orderByDesc('fecha')
      ->get()
      ->map(fn($g) => [
        $g->fecha, $g->mo, $g->cl, $g->modo, $g->prod, $g->p, $g->l, $g->pl, $g->w,
        $g->gh, $g->gs, $g->gi, $g->hum, $g->cz, $g->est, $g->abs, $g->fn, $g->punt, $g->particularidades,
      ]);

    return [
      new HojaExport('Pedidos activos', [
        'Pedido', 'Estado', 'Tipo', 'Fecha pedido', 'Molino', 'Cliente', 'Línea', 'Calidad', 'Tolvas', 'Bolsas',
        'Kilos', 'Precio', 'Condición', 'Planta', 'Entrega', 'Tentativa', 'Hora', 'Plazo', 'Forma de pago', 'Entregar', 'Obs.',
      ], $pedidos),
      new HojaExport('Entregas 14 días', [
        'Fecha', 'Hora', 'Pedido', 'Molino', 'Cliente', 'Calidad', 'Envase', 'Transportista', 'Chofer',
        'Hora carga', 'Kg molino', 'Kg cliente', 'Remito', 'Descargó',
      ], $proximas),
      new HojaExport('Calidad 90 días', [
        'Fecha', 'MO', 'CL', 'Modo', 'Prod', 'P', 'L', 'PL', 'W', 'GH', 'GS', 'GI', 'Hum', 'Cz', 'Est', 'Abs', 'FN', 'Punt', 'Particularidades',
      ], $calidad),
    ];
  }
}
