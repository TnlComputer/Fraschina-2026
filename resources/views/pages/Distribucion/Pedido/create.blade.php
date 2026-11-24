@extends('adminlte::page')

@section('title', 'Crear Pedido')

@section('content_header')
<h1>Nuevo Pedido</h1>
@endsection

@section('content')
<form id="pedido-form" action="{{ route('distribucion_pedido.store') }}" method="POST">
  @csrf

  <!-- Cliente -->
  <div class="form-group">
    <label for="cliente_id">Cliente:</label>
    <select name="cliente_id" id="cliente_id" class="form-control" required data-buscador>
      <option value="">Seleccione un cliente</option>
      @foreach($clientes as $cliente)
      <option value="{{ $cliente->id }}" @selected($clienteId === (int) $cliente->id)>{{ $cliente->id }} · {{ unir($cliente->nomfantasia, $cliente->razonsocial) }}</option>
      @endforeach
    </select>
  </div>

  <!-- Fechas -->
  <div class="row">
    <div class="col-md-6">
      <label for="fecha_pedido">Fecha del Pedido:</label>
      <input type="date" id="fecha_pedido" class="form-control" value="{{ date('Y-m-d') }}" readonly>
    </div>
    <div class="col-md-6">
      <label for="fecha_entrega">Fecha de Entrega:</label>
      <input type="date" name="fecha_entrega" id="fecha_entrega" class="form-control" value="{{ old('fecha_entrega') }}" required>
    </div>
  </div>

  @include('pages.Distribucion.Pedido.partials.productos')

  <h4 class="mt-3">Detalles del Pedido</h4>
  <div class="table-responsive">
    <table class="table table-bordered sin-tarjetas" id="detalleTable">
      @include('pages.Distribucion.Pedido.partials.encabezado')
      <tbody></tbody>
    </table>
  </div>
  <p class="text-right h5">Total c/IVA: <strong id="total-pedido">$ 0,00</strong></p>

  <!-- Campo de Observaciones -->
  <div class="form-group">
    <label for="observaciones">Observaciones:</label>
    <textarea name="observaciones" id="observaciones" class="form-control" rows="3">{{ old('observaciones') }}</textarea>
  </div>

  <button type="submit" class="btn btn-primary">Guardar Pedido</button>
</form>

@include('pages.Distribucion.Pedido.partials.script', ['modo' => 'alta', 'cliente' => null])
@endsection