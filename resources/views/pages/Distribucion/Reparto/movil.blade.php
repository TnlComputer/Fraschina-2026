@extends('adminlte::page')

@section('title', 'Reparto ' . fecha($fecha))

@section('content_header')
<div class="d-flex flex-wrap align-items-center">
  <h1 class="mb-2 mr-auto">Reparto {{ fecha($fecha) }}</h1>
  <a href="{{ route('distribucion_reparto.index', ['fecha' => $fecha]) }}" class="btn btn-outline-secondary btn-sm mb-2">
    <i class="fas fa-table"></i> Vista completa
  </a>
</div>
<form method="GET" class="d-flex flex-wrap" style="gap: .5rem">
  <input type="date" name="fecha" value="{{ $fecha }}" class="form-control" style="max-width: 180px" onchange="this.form.submit()">
  <select name="chofer" class="form-control" style="max-width: 160px" onchange="this.form.submit()">
    <option value="">Todos los choferes</option>
    @foreach ($choferes as $c)
    <option value="{{ $c }}" @selected(request('chofer') === $c)>Chofer {{ $c }}</option>
    @endforeach
  </select>
</form>
@endsection

@section('content')
@php $mapa = fn($texto) => 'https://www.google.com/maps/search/?api=1&query=' . urlencode($texto); @endphp

<p class="text-muted small mb-2">
  {{ $paradas->count() }} {{ $paradas->count() === 1 ? 'parada' : 'paradas' }} · Total {{ pesos($paradas->sum('totalPedido')) }}
</p>

@forelse ($paradas as $p)
@php
  $c = $p->distribucion;
  $calle = trim(($c->auxCalles?->calle ?? '') . ' ' . $c->dire_nro);
  $direccion = trim($calle . ($c->piso ? ' Piso ' . $c->piso : '') . ($c->dpto ? ' Dto ' . $c->dpto : ''));
  $zona = collect([$c->auxbarrio?->nombrebarrio, $c->auxlocalidad?->localidad])->filter()->implode(', ');
  $imprimir = \App\Models\Distribucion::esSi($c->fac_imp);
@endphp
<div class="card parada mb-3">
  <div class="card-header d-flex align-items-center py-2">
    <span class="parada-orden mr-2">{{ $p->orden ?: '–' }}</span>
    <div class="mr-auto">
      <strong>{{ $c->nomfantasia ?: $c->razonsocial }}</strong>
      <div class="small text-muted">Pedido #{{ $p->id }}{{ $p->chofer ? ' · Chofer ' . $p->chofer : '' }}</div>
    </div>
    @if ($imprimir)<span class="badge badge-warning" title="Llevar factura impresa"><i class="fas fa-print"></i> Factura</span>@endif
  </div>
  <div class="card-body py-2">
    @if ($direccion)
    <div class="mb-1">
      <i class="fas fa-location-dot text-danger mr-1"></i>{{ $direccion }}@if ($zona)<span class="text-muted"> · {{ $zona }}</span>@endif
    </div>
    @endif
    <div class="small mb-2">
      <i class="far fa-clock mr-1"></i>Recibe {{ horario($c->desde, $c->hasta, 'sin horario') }}
      @if (horario($c->desde1, $c->hasta1, '') !== '') y {{ horario($c->desde1, $c->hasta1) }}@endif
      @if (\App\Models\Distribucion::esSi($c->lunes)) · <strong>lunes cerrado</strong>@endif
      @if ($c->obsrecep)<div class="text-muted">{{ $c->obsrecep }}</div>@endif
    </div>

    @if ($p->lineasPedidos->isNotEmpty())
    <ul class="list-unstyled mb-2 parada-items">
      @foreach ($p->lineasPedidos as $l)
      <li><strong>{{ numero($l->cantidad) }}</strong> × {{ $l->producto?->productoCDA }}</li>
      @endforeach
    </ul>
    @endif
    @if ($p->lineasTareas->isNotEmpty())
    <ul class="list-unstyled mb-2 parada-items">
      @foreach ($p->lineasTareas as $t)
      <li><i class="fas fa-screwdriver-wrench text-secondary mr-1"></i>{{ $t->tarea?->tarea }}{{ $t->detalles ? ' — ' . $t->detalles : '' }}</li>
      @endforeach
    </ul>
    @endif

    <div class="small">
      @if ($p->nroFactura)Factura {{ $p->nroFactura }} · @endif
      <strong>{{ pesos($p->totalFactura ?: $p->totalPedido) }}</strong>
      @if ($c->auxtcobro?->nombre || $c->auxpago?->nombre) · {{ collect([$c->auxpago?->nombre, $c->auxtcobro?->nombre])->filter()->implode(' / ') }}@endif
      @if ($c->auxcobro?->accion) · Cobrar: {{ $c->auxcobro->accion }}@endif
    </div>
    @if ($p->observaciones)<div class="small text-muted mt-1">{{ $p->observaciones }}</div>@endif
  </div>
  <div class="card-footer d-flex py-2" style="gap: .5rem">
    @if ($direccion)
    <a href="{{ $mapa($direccion . ', ' . ($zona ?: 'Buenos Aires') . ', Argentina') }}" target="_blank" rel="noopener" class="btn btn-outline-primary flex-fill">
      <i class="fas fa-map-location-dot"></i> Mapa
    </a>
    @endif
    @php $tel = preg_replace('/[^0-9+]/', '', explode('/', (string) $c->telefono)[0]); @endphp
    @if ($tel)
    <a href="tel:{{ $tel }}" class="btn btn-outline-success flex-fill"><i class="fas fa-phone"></i> Llamar</a>
    @endif
    <a href="{{ route('distribucion_reparto.show', $p->id) }}" class="btn btn-outline-secondary flex-fill"><i class="fas fa-eye"></i> Detalle</a>
  </div>
</div>
@empty
<div class="alert alert-info">No hay pedidos para este día{{ request('chofer') ? ' y chofer' : '' }}.</div>
@endforelse
@endsection
