@extends('adminlte::page')

@section('title', 'Editar Pedido')

@section('content_header')
<h1>Editar Pedido #{{ $pedido->id }}</h1>
@endsection

@section('content')
<div class="card">
  <div class="card-body">
    <form action="{{ route('distribucion_pedido.update', $pedido->id) }}" method="POST" id="pedidoForm">
      @csrf
      @method('PUT')
      @php $volver = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) old('volver', request('volver'))) ? old('volver', request('volver')) : null; @endphp
      @if ($volver)<input type="hidden" name="volver" value="{{ $volver }}">@endif

      <!-- Información General -->
      <div class="row mb-3">
        <div class="col-md-4">
          <label>Cliente:</label>
          <input type="text" class="form-control" value="{{ $pedido->distribucion?->nomfantasia }}" disabled>
        </div>
        <div class="col-md-4">
          <label>Fecha de Entrega:</label>
          <input type="date" name="fecha_entrega" class="form-control" value="{{ $pedido->fechaEntrega }}" required>
        </div>
        <div class="col-md-4">
          <label>Observaciones:</label>
          <input type="text" name="observaciones" class="form-control" value="{{ $pedido->observaciones }}">
        </div>
      </div>

      <!-- Detalles del Pedido -->
      <h4>Detalles del Pedido</h4>
      <div class="table-responsive">
      <table class="table table-bordered sin-tarjetas" id="detalleTable">
        @include('pages.Distribucion.Pedido.partials.encabezado')
        <tbody>
          {{-- Productos existentes --}}
          @foreach($pedido->lineasPedidos as $linea)
          <tr data-linea-id="{{ $linea->id }}" data-iva="{{ $linea->producto?->porcentajeIva() ?? 21 }}" class="producto-row">
            <td>Producto</td>
            <td>
              {{ $linea->producto?->productoCDA }}
              <input type="hidden" name="existing_product_line_ids[]" value="{{ $linea->id }}">
            </td>
            <td>
              <input type="number" step="0.01" name="existing_precios[]" value="{{ $linea->precio_unitario }}"
                class="form-control" required>
            </td>
            <td>
              <input type="number" name="existing_cantidades[]" value="{{ $linea->cantidad }}" class="form-control"
                required>
            </td>
            @foreach (['bandera' => 'existing_flag', 'cambiar' => 'existing_sin_cargo', 'retirar' => 'existing_retirar'] as $campo => $nombre)
            @php $marca = \App\Models\DistribucionLineaPedidos::marcado($linea->$campo); @endphp
            <td class="text-center">
              <input type="hidden" name="{{ $nombre }}[]" value="{{ $marca ? 1 : 0 }}"><input type="checkbox" data-marca @checked($marca)>
            </td>
            @endforeach
            <td class="linea-total">{{ pesos($linea->totalLinea) }}</td>
            <td>
              <button type="button" class="btn btn-danger remove-row">X</button>
            </td>
          </tr>
          @endforeach

          {{-- Tareas existentes --}}
          @foreach($pedido->lineasTareas as $linea)
          <tr data-tarea-id="{{ $linea->id }}" class="tarea-row">
            <td>Tarea</td>
            <td>
              {{ $linea->tarea?->tarea }}
              <input type="hidden" name="existing_tarea_ids[]" value="{{ $linea->id }}">
              <input type="text" name="existing_tarea_detalles[]" value="{{ $linea->detalles }}" class="form-control form-control-sm" placeholder="Detalle">
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>
              <button type="button" class="btn btn-danger remove-row">X</button>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      </div>
      <p class="text-right h5">Total c/IVA: <strong id="total-pedido"></strong></p>

      @include('pages.Distribucion.Pedido.partials.productos')

      <!-- Los nuevos ítems se agregarán dinámicamente a la tabla -->
      <div class="form-acciones mt-4 pt-3 border-top">
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
        <a href="{{ route('distribucion_reparto.index', ['fecha' => $volver ?: $pedido->fechaEntrega]) }}"
          class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>

@include('pages.Distribucion.Pedido.partials.script', ['modo' => 'edicion', 'cliente' => $pedido->distribucion_id])
@endsection