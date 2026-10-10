@extends('adminlte::page')

@section('title', 'Certificados pendientes')

@section('content_header')
<h1>Certificados de calidad pendientes</h1>
@endsection

@section('content')
<p class="text-muted">Entregas ya descargadas cuyo certificado todavía no se recibió, agrupadas por molino para reclamarlos.</p>

@forelse ($entregas as $molino => $lista)
<div class="card card-danger card-outline">
  <div class="card-header"><h3 class="card-title">{{ $molino }} <span class="badge badge-danger">{{ $lista->count() }}</span></h3></div>
  <div class="card-body p-0">
    <table class="table table-sm mb-0">
      <thead><tr><th>Entrega</th><th>Pedido</th><th>Cliente</th><th>Calidad</th><th>Envase</th><th>Remito</th><th></th></tr></thead>
      <tbody>
        @foreach ($lista as $e)
        <tr>
          <td>{{ $e->linea->fecha_entrega?->format('d/m/Y') }}</td>
          <td><a href="{{ route('expedicion_pedido.show', $e->linea->pedido) }}">{{ $e->linea->pedido->codigo }}</a></td>
          <td>{{ $e->linea->pedido->cliente->sigla }}</td>
          <td>{{ $e->linea->calidad }}</td>
          <td>{{ $e->linea->envase }}</td>
          <td>{{ $e->nro_remito }}</td>
          <td><a href="{{ route('expedicion_control.index', ['fecha' => $e->linea->fecha_entrega?->toDateString()]) }}" class="btn btn-xs btn-outline-primary">Marcar recibido</a></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@empty
<div class="alert alert-success">No hay certificados pendientes.</div>
@endforelse
@endsection
