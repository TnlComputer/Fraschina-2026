@extends('adminlte::page')

@section('title', 'Diaria')

@section('content_header')
<h1>Diaria <small class="text-muted">registro de movimientos de pedidos</small></h1>
@endsection

@section('content')
<div class="card">
  <div class="card-body">
    <form method="GET" class="form-row align-items-end mb-3">
      <div class="col-md-2">
        <label class="small mb-0">Desde</label>
        <input type="date" name="desde" data-dias="exp_diaria" value="{{ request('desde') }}" class="form-control form-control-sm">
      </div>
      <div class="col-md-2">
        <label class="small mb-0">Hasta</label>
        <input type="date" name="hasta" data-dias="exp_diaria" value="{{ request('hasta') }}" class="form-control form-control-sm">
      </div>
      <div class="col-md-2">
        <label class="small mb-0">N° de pedido</label>
        <input type="number" name="numero" value="{{ request('numero') }}" class="form-control form-control-sm">
      </div>
      <div class="col-md-2">
        <label class="small mb-0">Acción</label>
        <select name="accion" class="form-control form-control-sm">
          <option value="">Todas</option>
          @foreach ($acciones as $clave => $nombre)
          <option value="{{ $clave }}" @selected(request('accion') === $clave)>{{ $nombre }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <button class="btn btn-sm btn-secondary">Filtrar</button>
        <a href="{{ route('expedicion_diaria.index') }}" class="btn btn-sm btn-link">Limpiar</a>
      </div>
    </form>

    <table class="table table-sm table-striped">
      <thead class="thead-light">
        <tr><th>Fecha y hora</th><th>Pedido</th><th>Molino</th><th>Cliente</th><th>Acción</th><th>Detalle</th><th>Usuario</th></tr>
      </thead>
      <tbody>
        @forelse ($movimientos as $mov)
        <tr>
          <td class="text-nowrap small">{{ $mov->created_at?->format('d/m/Y H:i') }}</td>
          <td class="text-nowrap">
            <a href="{{ route('expedicion_pedido.show', $mov->pedido_id) }}">{{ $mov->numero ? $mov->numero . '-' . $mov->revision : 'PEND-' . $mov->pedido_id }}</a>
          </td>
          <td>{{ $mov->pedido?->molino?->sigla }}</td>
          <td>{{ $mov->pedido?->cliente?->sigla }}</td>
          <td><span class="badge badge-light">{{ $mov->accion_nombre }}</span></td>
          <td class="small" style="white-space: pre-line">{{ $mov->detalle }}</td>
          <td class="small">{{ $mov->usuario?->name }}</td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center text-muted">Sin movimientos.</td></tr>
        @endforelse
      </tbody>
    </table>

    {{ $movimientos->links() }}
  </div>
</div>
@endsection

@include('partials.calendario-dias')
