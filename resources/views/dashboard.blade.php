@extends('adminlte::page')

@section('title', 'Inicio')

@php
$hora = now()->hour;
$saludo = $hora < 12 ? 'Buenos días' : ($hora < 20 ? 'Buenas tardes' : 'Buenas noches');
$pesos = fn($n) => '$' . number_format((float) $n, 2, ',', '.');
@endphp

@section('content_header')
<h1>{{ $saludo }}, {{ $usuario->name }}</h1>
<div class="inicio-saludo">{{ ucfirst(now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY')) }}</div>
@endsection

@section('content')
@isset($distribucion)
<h5 class="mb-3"><i class="fas fa-truck text-primary mr-1"></i> Distribución</h5>
<div class="row">
  <div class="col-lg-3 col-6">
    <a href="{{ route('distribucion_reparto.index', ['fecha' => $distribucion['proximo']]) }}" class="small-box bg-primary d-block inicio-tarjeta">
      <div class="inner">
        <h3>{{ $distribucion['pedidos'] }}</h3>
        <p>Pedidos para el {{ $distribucion['proximo'] ? \Carbon\Carbon::parse($distribucion['proximo'])->locale('es')->isoFormat('dddd D/MM') : 'próximo reparto' }}</p>
      </div>
      <div class="icon"><i class="fas fa-truck-fast"></i></div>
      <span class="small-box-footer">Ver reparto <i class="fas fa-arrow-circle-right"></i></span>
    </a>
  </div>
  <div class="col-lg-3 col-6">
    <a href="{{ route('distribucion_reparto.index', ['fecha' => $distribucion['proximo']]) }}" class="small-box {{ $distribucion['sinFactura'] ? 'bg-warning' : 'bg-success' }} d-block inicio-tarjeta">
      <div class="inner">
        <h3>{{ $distribucion['sinFactura'] }}</h3>
        <p>Pedidos sin factura cargada</p>
      </div>
      <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
      <span class="small-box-footer">Total del reparto: {{ $pesos($distribucion['total']) }}</span>
    </a>
  </div>
  <div class="col-lg-3 col-6">
    <a href="{{ route('distribucion_reparto.index', ['fecha' => $distribucion['proximo']]) }}" class="small-box {{ $distribucion['sinChofer'] ? 'bg-warning' : 'bg-success' }} d-block inicio-tarjeta">
      <div class="inner">
        <h3>{{ $distribucion['sinChofer'] }}</h3>
        <p>Pedidos sin chofer asignado</p>
      </div>
      <div class="icon"><i class="fas fa-user-clock"></i></div>
      <span class="small-box-footer">Asignar chofer y orden <i class="fas fa-arrow-circle-right"></i></span>
    </a>
  </div>
  <div class="col-lg-3 col-6">
    <a href="{{ route('distribucion_agenda.index') }}" class="small-box bg-info d-block inicio-tarjeta">
      <div class="inner">
        <h3>{{ $distribucion['agendaHoy'] }}</h3>
        <p>Contactos agendados para hoy</p>
      </div>
      <div class="icon"><i class="fas fa-phone"></i></div>
      <span class="small-box-footer">Ver agenda <i class="fas fa-arrow-circle-right"></i></span>
    </a>
  </div>
</div>

<div class="row">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header"><h3 class="card-title">Últimos pedidos cargados</h3></div>
      <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0 table-no-sticky">
          <thead><tr><th>Pedido</th><th>Cliente</th><th>Entrega</th><th class="text-right">Total</th><th></th></tr></thead>
          <tbody>
            @forelse ($distribucion['ultimosPedidos'] as $p)
            <tr>
              <td>#{{ $p->id }}</td>
              <td>{{ $p->distribucion?->nomfantasia }}</td>
              <td>{{ $p->fechaEntrega ? \Carbon\Carbon::parse($p->fechaEntrega)->format('d/m/Y') : '—' }}</td>
              <td class="text-right">{{ $pesos($p->totalPedido) }}</td>
              <td class="text-right"><a href="{{ route('distribucion_reparto.show', $p->id) }}" class="btn btn-xs btn-outline-primary" title="Ver"><i class="fas fa-eye"></i></a></td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted">Sin pedidos.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header"><h3 class="card-title">Accesos rápidos</h3></div>
      <div class="card-body d-grid">
        <a href="{{ route('distribucion_pedido.create') }}" class="btn btn-primary btn-block text-left"><i class="fas fa-plus mr-2"></i>Nuevo pedido</a>
        <a href="{{ route('distribucion_agenda.create') }}" class="btn btn-outline-primary btn-block text-left"><i class="fas fa-phone mr-2"></i>Nuevo contacto en la agenda</a>
        <a href="{{ route('distribucion_reparto.index') }}" class="btn btn-outline-primary btn-block text-left"><i class="fas fa-truck mr-2"></i>Reparto de hoy</a>
        <a href="{{ route('distribucion.index') }}" class="btn btn-outline-primary btn-block text-left"><i class="fas fa-store mr-2"></i>Clientes</a>
      </div>
    </div>
  </div>
</div>
@endisset

@isset($expedicion)
<h5 class="mb-3 mt-2"><i class="fas fa-industry text-primary mr-1"></i> Central de Pedidos</h5>
<div class="row">
  @foreach ([
    ['n' => $expedicion['pendientes'], 't' => 'Pendientes de confirmación', 'c' => 'bg-info', 'i' => 'fa-hourglass-half', 'u' => route('expedicion_pedido.index', ['estado' => 'pendiente_confirmacion'])],
    ['n' => $expedicion['sinEnviar'], 't' => 'Confirmados sin enviar al molino', 'c' => $expedicion['sinEnviar'] ? 'bg-warning' : 'bg-success', 'i' => 'fa-paper-plane', 'u' => route('expedicion.index')],
    ['n' => $expedicion['entregasHoy'], 't' => 'Entregas de hoy', 'c' => 'bg-primary', 'i' => 'fa-truck-ramp-box', 'u' => route('expedicion_control.index')],
    ['n' => $expedicion['certificados'], 't' => 'Certificados de calidad pendientes', 'c' => $expedicion['certificados'] ? 'bg-danger' : 'bg-success', 'i' => 'fa-file-signature', 'u' => route('expedicion_informes.certificados')],
  ] as $t)
  <div class="col-lg-3 col-6">
    <a href="{{ $t['u'] }}" class="small-box {{ $t['c'] }} d-block inicio-tarjeta">
      <div class="inner"><h3>{{ $t['n'] }}</h3><p>{{ $t['t'] }}</p></div>
      <div class="icon"><i class="fas {{ $t['i'] }}"></i></div>
      <span class="small-box-footer">Ver <i class="fas fa-arrow-circle-right"></i></span>
    </a>
  </div>
  @endforeach
</div>
@endisset

@isset($auditoria)
<div class="card">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title"><i class="fas fa-clipboard-check mr-1"></i> Últimas operaciones</h3>
    <a href="{{ route('auditoria.index') }}" class="ml-auto small">Ver auditoría completa</a>
  </div>
  <div class="card-body p-0">
    <ul class="list-unstyled mb-0 px-3 lista-compacta">
      @forelse ($auditoria as $a)
      <li class="d-flex">
        <span class="text-muted small mr-3 text-nowrap">{{ $a->created_at?->format('d/m H:i') }}</span>
        <span class="small"><strong>{{ $a->usuario ?? '—' }}</strong> · {{ $a->accion_nombre }} · {{ $a->sector }} · {{ $a->descripcion }}</span>
      </li>
      @empty
      <li class="text-muted small">Sin operaciones registradas.</li>
      @endforelse
    </ul>
  </div>
</div>
@endisset

@if (!isset($distribucion) && !isset($expedicion) && !isset($auditoria))
<div class="card"><div class="card-body">Usá el menú de la izquierda para ir a tu sección.</div></div>
@endif
@endsection
