@extends('adminlte::page')

@section('title', 'Central de Pedidos')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
  <h1>Central de Pedidos</h1>
  @can('permiso_11')<a href="{{ route('expedicion_pedido.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo pedido</a>@endcan
</div>
@endsection

@section('content')
<div class="row">
  <div class="col-md-3 col-6">
    <a href="{{ route('expedicion_pedido.index', ['estado' => 'pendiente_confirmacion']) }}" class="small-box bg-info d-block">
      <div class="inner">
        <h3>{{ $pendientesConfirmacion->count() }}</h3>
        <p>Pendientes de confirmación</p>
      </div>
      <div class="icon"><i class="fas fa-hourglass-half"></i></div>
    </a>
  </div>
  <div class="col-md-3 col-6">
    <a href="#sin-enviar" class="small-box bg-warning d-block">
      <div class="inner">
        <h3>{{ $sinEnviar->count() }}</h3>
        <p>Confirmados sin enviar al molino</p>
      </div>
      <div class="icon"><i class="fas fa-paper-plane"></i></div>
    </a>
  </div>
  <div class="col-md-3 col-6">
    <a href="{{ route('expedicion_planificacion.index') }}" class="small-box bg-primary d-block">
      <div class="inner">
        <h3>{{ $tentativas }}</h3>
        <p>Entregas con fecha tentativa</p>
      </div>
      <div class="icon"><i class="far fa-calendar-alt"></i></div>
    </a>
  </div>
  <div class="col-md-3 col-6">
    <a href="{{ route('expedicion_informes.certificados') }}" class="small-box bg-danger d-block">
      <div class="inner">
        <h3>{{ $certificadosPendientes }}</h3>
        <p>Certificados de calidad pendientes</p>
      </div>
      <div class="icon"><i class="fas fa-file-signature"></i></div>
    </a>
  </div>
</div>

<div class="row">
  <div class="col-lg-6">
    <div class="card card-info card-outline">
      <div class="card-header"><h3 class="card-title">Pendientes de confirmación</h3></div>
      <div class="card-body p-0">
        @include('pages.Expedicion.partials.tabla-pedidos', ['lista' => $pendientesConfirmacion, 'vacio' => 'No hay pedidos pendientes de confirmación.'])
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card card-primary card-outline">
      <div class="card-header"><h3 class="card-title">Pendientes de cierre</h3></div>
      <div class="card-body p-0">
        @include('pages.Expedicion.partials.tabla-pedidos', ['lista' => $pendientesCierre, 'vacio' => 'No hay pedidos pendientes de cierre.'])
      </div>
    </div>
  </div>
</div>

<div class="row">
  @foreach (['Entregas de hoy' => $entregasHoy, 'Entregas de mañana' => $entregasManana] as $titulo => $entregas)
  <div class="col-lg-6">
    <div class="card card-success card-outline">
      <div class="card-header d-flex justify-content-between">
        <h3 class="card-title">{{ $titulo }}</h3>
        <a href="{{ route('expedicion_control.index') }}" class="ml-auto small">Ir al control de entregas</a>
      </div>
      <div class="card-body p-0">
        <table class="table table-sm table-striped mb-0">
          <thead><tr><th>Pedido</th><th>Molino</th><th>Cliente</th><th>Calidad</th><th>Envase</th><th>Hora</th><th>Descarga</th></tr></thead>
          <tbody>
            @forelse ($entregas as $linea)
            <tr class="{{ $linea->fecha_tentativa ? 'text-primary' : '' }}">
              <td><a href="{{ route('expedicion_pedido.show', $linea->pedido) }}">{{ $linea->pedido->codigo }}</a></td>
              <td>{{ $linea->pedido->molino->sigla }}</td>
              <td>{{ $linea->pedido->cliente->sigla }}</td>
              <td>{{ $linea->calidad }}</td>
              <td>{{ $linea->envase }}</td>
              <td>{{ $linea->hora }}</td>
              <td>{!! $linea->entrega?->descarga_completa ? '<i class="fas fa-check text-success"></i>' : '' !!}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-muted text-center">Sin entregas.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
  @endforeach
</div>

<div class="card card-warning card-outline" id="sin-enviar">
  <div class="card-header"><h3 class="card-title">Confirmados sin enviar al molino</h3></div>
  <div class="card-body p-0">
    @include('pages.Expedicion.partials.tabla-pedidos', ['lista' => $sinEnviar, 'vacio' => 'Todos los pedidos confirmados fueron enviados.'])
  </div>
</div>
@endsection
