@extends('adminlte::page')

@section('title', 'Planificación semanal')

@section('content_header')
<div class="d-flex flex-wrap justify-content-between align-items-center">
  <h1>Planificación semanal</h1>
  <form method="GET" class="form-inline">
    <a href="{{ route('expedicion_planificacion.index', ['semana' => $inicio->copy()->subWeek()->toDateString(), 'molino_id' => request('molino_id')]) }}" class="btn btn-sm btn-outline-secondary mr-1">&laquo; Anterior</a>
    <input type="date" name="semana" value="{{ $inicio->toDateString() }}" class="form-control form-control-sm mr-1">
    <select name="molino_id" class="form-control form-control-sm mr-1">
      <option value="">Todos los molinos</option>
      @foreach ($molinos as $m)
      <option value="{{ $m->id }}" @selected(request('molino_id') == $m->id)>{{ $m->sigla }}</option>
      @endforeach
    </select>
    <button class="btn btn-sm btn-secondary mr-1">Ver</button>
    <a href="{{ route('expedicion_planificacion.index', ['semana' => $inicio->copy()->addWeek()->toDateString(), 'molino_id' => request('molino_id')]) }}" class="btn btn-sm btn-outline-secondary">Siguiente &raquo;</a>
  </form>
</div>
@endsection

@section('content')
<p class="small">
  <span class="badge badge-primary">Azul</span> fecha tentativa o pedido pendiente de confirmación / cierre.
</p>

<div class="row">
  @foreach ($dias as $dia)
  @php $delDia = $porDia->get($dia->toDateString(), collect()); @endphp
  <div class="col-lg col-md-6 mb-3">
    <div class="card h-100 {{ $dia->isToday() ? 'card-success card-outline' : '' }}">
      <div class="card-header py-2">
        <strong class="text-capitalize">{{ $dia->locale('es')->isoFormat('dddd D/M') }}</strong>
        <span class="float-right small text-muted">{{ $delDia->sum('tolvas') }}T · {{ $delDia->sum('bolsas') }}B</span>
      </div>
      <div class="card-body p-2">
        @forelse ($delDia as $l)
        @php $azul = $l->fecha_tentativa || in_array($l->pedido->estado, ['pendiente_confirmacion', 'pendiente_cierre']); @endphp
        <a href="{{ route('expedicion_pedido.show', $l->pedido) }}" class="d-block border rounded p-1 mb-1 small {{ $azul ? 'bg-primary text-white' : 'bg-light text-dark' }}">
          <strong>{{ $l->pedido->molino->sigla }} → {{ $l->pedido->cliente->sigla }}</strong>
          <span class="float-right">{{ $l->hora }}</span><br>
          {{ $l->calidad }} · {{ $l->envase }}
          @if ($l->condicion)<br>{{ $l->condicion }}@endif
          <br><span class="opacity-75">{{ $l->pedido->codigo }}</span>
          @if ($l->nota_fecha)<br><em>{{ $l->nota_fecha }}</em>@endif
        </a>
        @empty
        <div class="text-muted small text-center">—</div>
        @endforelse
      </div>
    </div>
  </div>
  @endforeach
</div>

@if ($sinFecha->isNotEmpty())
<div class="card card-primary card-outline">
  <div class="card-header"><h3 class="card-title">Entregas con fecha a confirmar</h3></div>
  <div class="card-body p-0">
    <table class="table table-sm mb-0">
      <thead><tr><th>Pedido</th><th>Molino</th><th>Cliente</th><th>Calidad</th><th>Envase</th><th>Nota</th></tr></thead>
      <tbody>
        @foreach ($sinFecha as $l)
        <tr class="text-primary">
          <td><a href="{{ route('expedicion_pedido.show', $l->pedido) }}">{{ $l->pedido->codigo }}</a></td>
          <td>{{ $l->pedido->molino->sigla }}</td>
          <td>{{ $l->pedido->cliente->sigla }}</td>
          <td>{{ $l->calidad }}</td>
          <td>{{ $l->envase }}</td>
          <td>{{ $l->nota_fecha }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif
@endsection
