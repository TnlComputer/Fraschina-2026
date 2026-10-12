@extends('adminlte::page')

@section('title', 'Historial de planillas')

@section('content_header')
<h1>Historial de planillas</h1>
@endsection

@php
$cli = fn($n) => $n ? $n . ' ' . ($clientes[$n] ?? '') : '';
$mol = fn($c) => $c ? $c . ' ' . ($molinos[$c] ?? '') : '';
@endphp

@section('content')
<p class="text-muted mb-2">Lo que se cargaba en las planillas <strong>Diaria</strong> (movimientos de pedidos) y <strong>Ctrol</strong> (control de entregas), tal cual estaba. Es solo de consulta.</p>

<ul class="nav nav-tabs mb-3">
  <li class="nav-item"><a class="nav-link {{ $vista === 'diaria' ? 'active' : '' }}" href="{{ route('expedicion_historial.index', ['vista' => 'diaria']) }}">Diaria</a></li>
  <li class="nav-item"><a class="nav-link {{ $vista === 'control' ? 'active' : '' }}" href="{{ route('expedicion_historial.index', ['vista' => 'control']) }}">Control de entregas</a></li>
</ul>

<form method="GET" class="card card-body py-2 mb-3">
  <input type="hidden" name="vista" value="{{ $vista }}">
  <div class="form-row align-items-end">
    @if ($vista === 'diaria')
    <div class="col-6 col-md-2 mb-2">
      <label class="small mb-0">Fecha de</label>
      <select name="por" class="form-control form-control-sm">
        <option value="registro">Registro</option>
        <option value="entrega" @selected(request('por') === 'entrega')>Entrega</option>
      </select>
    </div>
    @endif
    <div class="col-6 col-md-2 mb-2"><label class="small mb-0">Desde</label>
      <input type="date" name="desde" value="{{ request('desde') }}" class="form-control form-control-sm" data-dias="{{ $vista === 'control' ? 'hist_control' : 'hist_diaria' }}"></div>
    <div class="col-6 col-md-2 mb-2"><label class="small mb-0">Hasta</label>
      <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control form-control-sm" data-dias="{{ $vista === 'control' ? 'hist_control' : 'hist_diaria' }}"></div>
    <div class="col-6 col-md-2 mb-2"><label class="small mb-0">Molino</label>
      <select name="molino" class="form-control form-control-sm"><option value="">Todos</option>
        @foreach ($molinos as $codigo => $sigla)<option value="{{ $codigo }}" @selected(request('molino') == $codigo)>{{ $codigo }} {{ $sigla }}</option>@endforeach
      </select></div>
    <div class="col-6 col-md-2 mb-2"><label class="small mb-0">Cliente</label>
      <select name="cliente" class="form-control form-control-sm"><option value="">Todos</option>
        @foreach ($clientes as $numero => $nombre)<option value="{{ $numero }}" @selected(request('cliente') == $numero)>{{ $numero }} {{ $nombre }}</option>@endforeach
      </select></div>
    @if ($vista === 'diaria')
    <div class="col-6 col-md-2 mb-2"><label class="small mb-0">Acción</label>
      <select name="accion" class="form-control form-control-sm"><option value="">Todas</option>
        @foreach ($acciones as $codigo => $texto)<option value="{{ $codigo }}" @selected(request('accion') === $codigo)>{{ $texto }}</option>@endforeach
      </select></div>
    <div class="col-6 col-md-2 mb-2"><label class="small mb-0">N.º pedido</label>
      <input type="text" name="pedido" value="{{ request('pedido') }}" class="form-control form-control-sm"></div>
    @endif
    <div class="col-12 col-md-3 mb-2"><label class="small mb-0">Buscar</label>
      <input type="text" name="buscar" value="{{ request('buscar') }}" class="form-control form-control-sm" placeholder="{{ $vista === 'control' ? 'Cliente/calidad, transporte, estado…' : 'Observaciones, calidad…' }}"></div>
    <div class="col-auto mb-2 form-acciones">
      <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i> Filtrar</button>
      <a href="{{ route('expedicion_historial.index', ['vista' => $vista]) }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
    </div>
  </div>
</form>

<div class="card">
  <div class="card-body p-0 table-responsive">
    <table class="table table-sm table-striped mb-0 small">
      <thead>
        @if ($vista === 'diaria')
        <tr><th>Fecha</th><th>Hora</th><th>Molino</th><th>Pedido</th><th>RA</th><th>Cliente</th><th>*</th><th>Entrega</th><th>H.E.</th><th>Orden</th><th>PL</th><th>Calidad</th><th>T</th><th>B</th><th>Precio</th><th>Acción</th><th>Observaciones</th></tr>
        @else
        <tr><th>Fecha</th><th>Molino</th><th>Cliente</th><th>C (tal cual)</th><th>Transporte</th><th>H.C.</th><th>Estado</th><th></th><th>Kg mol.</th><th>Kg cte.</th><th>Dif.</th><th>Rto.</th></tr>
        @endif
      </thead>
      <tbody>
        @forelse ($registros as $r)
        @if ($vista === 'diaria')
        <tr>
          <td class="text-nowrap">{{ fecha($r->fecha) }}</td><td>{{ $r->hora }}</td><td class="text-nowrap">{{ $mol($r->molino_codigo) }}</td>
          <td>{{ $r->pedido }}</td><td>{{ $r->ra }}</td><td class="text-nowrap">{{ $cli($r->cliente_numero) }}</td><td>{{ $r->marca }}</td>
          <td class="text-nowrap">{{ $r->fecha_entrega ? fecha($r->fecha_entrega) : '' }}</td><td>{{ $r->hora_entrega }}</td><td>{{ $r->orden }}</td><td>{{ $r->planta }}</td>
          <td>{{ $r->calidad }}</td><td>{{ $r->tolvas }}</td><td>{{ $r->bolsas }}</td><td>{{ $r->precio }}</td>
          <td title="{{ $acciones[$r->accion] ?? '' }}">{{ $r->accion }}</td><td>{{ $r->observaciones }}</td>
        </tr>
        @else
        <tr>
          <td class="text-nowrap">{{ fecha($r->fecha) }}</td><td class="text-nowrap">{{ $mol($r->molino_codigo) }}</td><td class="text-nowrap">{{ $cli($r->cliente_numero) }}</td>
          <td>{{ $r->detalle }}</td><td>{{ $r->transporte }}</td><td>{{ $r->hora_carga }}</td><td>{{ $r->estado }}</td><td>{{ $r->confirmado }}</td>
          <td>{{ $r->kg_molino }}</td><td>{{ $r->kg_cliente }}</td><td>{{ $r->kg_diferencia }}</td><td>{{ $r->remito }}</td>
        </tr>
        @endif
        @empty
        <tr><td colspan="17" class="text-center text-muted py-3">No hay registros con esos filtros.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
{{ $registros->links() }}
@endsection

@include('partials.calendario-dias')
