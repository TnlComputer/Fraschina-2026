@extends('adminlte::page')

@section('title', 'Ver Pedido')

@section('content')
@php
$total = 0;
$cliente = $pedido->distribucion;
// Cambiar / retirar se guardan como '*' o 'SI' (igual que el sistema anterior)
$marcado = fn($v) => in_array(strtoupper(trim((string) $v)), ['*', 'SI', 'S'], true);
// Día de Reparto desde el que se abrió el pedido: Editar y Volver regresan ahí
$volver = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) request('volver')) ? request('volver') : $pedido->fechaEntrega;
@endphp
<div class="container">
  <h1>Detalles del Pedido</h1>

  <div class="card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <span>Pedido {{ $pedido->id }} · Entrega {{ fecha($pedido->fechaEntrega) }}
        @if ($pedido->status === 'D')<span class="badge badge-secondary ml-2">Eliminado</span>@endif
      </span>
      @if ($pedido->status !== 'D')
      <div class="d-flex">
        @can('permiso_12')
        <a href="{{ route('distribucion_pedido.edit', ['distribucion_pedido' => $pedido->id, 'volver' => $volver]) }}"
          class="btn btn-sm btn-light" title="Editar Pedido">
          <i class="fa-solid fa-pen-to-square text-blue"></i> Editar
        </a>
        @endcan
        @can('permiso_13')
        {{-- Baja lógica: el pedido sale del reparto pero queda en la base --}}
        <form method="POST" action="{{ route('distribucion_pedido.destroy', ['distribucion_pedido' => $pedido->id, 'volver' => $volver]) }}"
          class="ml-2" onsubmit="return confirm('¿Eliminar el pedido {{ $pedido->id }}? Sale del reparto y de las impresiones (queda guardado en el historial).');">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-sm btn-danger" title="Eliminar Pedido (por ejemplo, si se cargó dos veces)">
            <i class="fa-solid fa-trash"></i> Eliminar
          </button>
        </form>
        @endcan
      </div>
      @endif
    </div>
    <div class="card-body">
      <div class="row">
        {{-- Datos para hacer la factura: los mismos que mostraba la grilla de reparto del sistema anterior --}}
        <div class="col-md-6">
          <table class="table table-sm table-bordered">
            <tr>
              <th style="width: 35%">Nombre Fantasía</th>
              <td>{{ $cliente?->nomfantasia }}</td>
            </tr>
            <tr>
              <th>Razón Social</th>
              <td>{{ $cliente?->razonsocial }}</td>
            </tr>
            <tr>
              <th>CUIT</th>
              <td>{{ $cliente?->cuit ?: '—' }}</td>
            </tr>
            <tr>
              <th>Dirección</th>
              <td>
                {{ $cliente?->auxCalles?->calle }} {{ $cliente?->dire_nro }} {{ $cliente?->piso }} {{ $cliente?->dpto }}<br>
                {{ unir($cliente?->auxbarrio?->nombrebarrio, $cliente?->auxmunicipio?->ciudadmunicipio, $cliente?->auxlocalidad?->localidad) }}
                @if ($cliente?->auxzona?->nombre)
                · Zona {{ $cliente->auxzona->nombre }}
                @endif
              </td>
            </tr>
          </table>
        </div>
        <div class="col-md-6">
          <table class="table table-sm table-bordered">
            <tr>
              <th style="width: 35%">Cobrar</th>
              <td>{{ trim((string) $cliente?->auxcobro?->accion) ?: '—' }}</td>
            </tr>
            <tr>
              <th>Cobranza</th>
              <td>{{ trim((string) $cliente?->auxpago?->nombre) ?: '—' }}</td>
            </tr>
            <tr>
              <th>Modo de Pago</th>
              <td>{{ trim((string) $cliente?->auxtcobro?->nombre) ?: '—' }}</td>
            </tr>
            <tr>
              <th>Factura</th>
              <td>
                {{ $pedido->nroFactura ?: '—' }} · {{ fecha($pedido->fechaFactura) }} · {{ pesos($pedido->totalFactura, 2) }}
                @if ((float) $pedido->totalFactura != 0 && abs($pedido->totalFactura - $pedido->totalPedido) > 0.01)
                <span class="badge badge-danger ml-1" title="Importe de factura menos total del pedido">
                  Diferencia {{ pesos($pedido->totalFactura - $pedido->totalPedido, 2) }}
                </span>
                @endif
              </td>
            </tr>
          </table>
        </div>
      </div>
      <table class="table table-sm table-bordered mb-0">
        <tr>
          <th class="w-25">Horario Entrega</th>
          <td>
            Mañana: {{ horario($cliente?->desde, $cliente?->hasta) }} · Tarde: {{ horario($cliente?->desde1, $cliente?->hasta1) }}
            · Lun cerrado: {{ $cliente?->lunes ?: '—' }} · Sáb recibe: {{ $cliente?->sabado ?: '—' }}
          </td>
        </tr>
        <tr>
          <th>Observaciones Recepción</th>
          <td>{{ $cliente?->obsrecep ?: '—' }}</td>
        </tr>
        <tr>
          <th>Comentarios del Pedido</th>
          <td>{{ $pedido->observaciones ?: '—' }}</td>
        </tr>
      </table>
    </div>
  </div>

  <div class="mt-4">
    <h4>Detalles de los productos</h4>
    <table class="table table-sm table-striped table-bordered">
      <thead class="thead-dark">
        <tr>
          <th>Producto</th>
          <th class=" text-center">Cantidad</th>
          <th class=" text-right">Precio Unitario</th>
          <th class=" text-right">Subtotal</th>
          <th class=" text-right">c/Iva</th>
          <th class=" text-center">Cambiar / Retirar</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($pedido->lineasPedidos as $detalle)
        @php
        $precio = $detalle->cantidad * $detalle->precio_unitario;
        $precioConIva = \App\Models\productoCDA::calcularTotalLinea($detalle->precio_unitario, $detalle->cantidad, $detalle->producto?->porcentajeIva() ?? 21);
        $total = $total + $precioConIva;
        @endphp
        <tr>
          <td>{{ $detalle->producto?->productoCDA }}</td>
          <td class=" text-center">{{ numero($detalle->cantidad, 0) }}</td>
          <td class=" text-right">${{ numero($detalle->precio_unitario, 2) }}</td>
          <td class=" text-right">${{ numero($precio, 2) }}</td>
          <td class=" text-right">${{ numero($precioConIva, 2) }}</td>
          <td class=" text-center">
            @if ($marcado($detalle->cambiar))
            <span class="badge badge-warning">SIN CARGO</span>
            @endif
            @if ($marcado($detalle->retirar))
            <span class="badge badge-info">RETIRAR</span>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <th colspan="4" class="text-right">Total</th>
          <td class="text-right">${{ numero($total, 2) }}</td>
          <td></td>
        </tr>
      </tfoot>
    </table>
  </div>

  @if ($pedido->lineasTareas->isNotEmpty())
  <div class="mt-4">
    <h4>Tareas</h4>
    <ul>
      @foreach ($pedido->lineasTareas as $t)
      <li>{{ $t->tarea?->tarea }}{{ $t->detalles ? ' — ' . $t->detalles : '' }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  <div class="mt-4">
    <a href="{{ route('distribucion_reparto.index', ['fecha' => $volver]) }}" class="btn btn-secondary">
      <i class="fas fa-arrow-left"></i> Volver
    </a>
  </div>
</div>
@endsection
