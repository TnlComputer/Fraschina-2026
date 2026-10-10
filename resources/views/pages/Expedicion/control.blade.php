@extends('adminlte::page')

@section('title', 'Control de entregas')

@section('content_header')
<div class="d-flex flex-wrap justify-content-between align-items-center">
  <h1>Control de entregas</h1>
  <form method="GET" class="form-inline">
    <a href="{{ route('expedicion_control.index', ['fecha' => $fecha->copy()->subDay()->toDateString()]) }}" class="btn btn-sm btn-outline-secondary mr-1">&laquo;</a>
    <input type="date" name="fecha" value="{{ $fecha->toDateString() }}" class="form-control form-control-sm mr-1">
    <button class="btn btn-sm btn-secondary mr-1">Ver</button>
    <a href="{{ route('expedicion_control.index', ['fecha' => $fecha->copy()->addDay()->toDateString()]) }}" class="btn btn-sm btn-outline-secondary">&raquo;</a>
  </form>
</div>
@endsection

@section('content')
@foreach ([$fecha, $siguiente] as $dia)
@php $delDia = $lineas->get($dia->toDateString(), collect()); @endphp
<div class="card {{ $dia->isToday() ? 'card-success' : 'card-secondary' }} card-outline">
  <div class="card-header">
    <h3 class="card-title text-capitalize">
      {{ $dia->isToday() ? 'Hoy' : ($dia->isTomorrow() ? 'Mañana' : '') }} {{ $dia->locale('es')->isoFormat('dddd D/MM/YYYY') }}
    </h3>
    <span class="float-right small">{{ $delDia->count() }} entregas</span>
  </div>
  <div class="card-body p-0 table-responsive">
    <table class="table table-sm table-bordered mb-0 text-sm">
      <thead class="thead-light">
        <tr>
          <th>Pedido</th><th>Molino</th><th>Cliente</th><th>Calidad / envase</th>
          <th>Transportista</th><th>Chofer</th><th>Carga</th><th>Hora carga</th><th>Llegada</th>
          <th>Kg mol.</th><th>Kg cli.</th><th>Dif.</th><th>Remito</th><th>Descargó</th><th>Certif.</th><th>Observaciones</th><th></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($delDia as $l)
        @php $e = $l->entrega; $form = 'entrega-' . $l->id; @endphp
        <tr class="{{ $l->fecha_tentativa ? 'text-primary' : '' }}">
          <td class="text-nowrap"><a href="{{ route('expedicion_pedido.show', $l->pedido) }}">{{ $l->pedido->codigo }}</a></td>
          <td>{{ $l->pedido->molino->sigla }}</td>
          <td>{{ $l->pedido->cliente->sigla }}</td>
          <td>{{ $l->calidad }}<br><small>{{ $l->envase }} {{ $l->hora ? '· ' . $l->hora : '' }}</small></td>
          <td><input form="{{ $form }}" name="transportista" value="{{ $e?->transportista }}" class="form-control form-control-sm" maxlength="100"></td>
          <td><input form="{{ $form }}" name="chofer" value="{{ $e?->chofer }}" class="form-control form-control-sm" maxlength="100"></td>
          <td><input form="{{ $form }}" type="date" name="fecha_carga" value="{{ $e?->fecha_carga?->format('Y-m-d') }}" class="form-control form-control-sm"></td>
          <td><input form="{{ $form }}" name="hora_carga" value="{{ $e?->hora_carga }}" class="form-control form-control-sm" style="width: 70px" maxlength="20"></td>
          <td><input form="{{ $form }}" name="hora_llegada" value="{{ $e?->hora_llegada }}" class="form-control form-control-sm" style="width: 70px" maxlength="20"></td>
          <td><input form="{{ $form }}" type="number" min="0" name="kg_molino" value="{{ $e?->kg_molino }}" class="form-control form-control-sm" style="width: 90px"></td>
          <td><input form="{{ $form }}" type="number" min="0" name="kg_cliente" value="{{ $e?->kg_cliente }}" class="form-control form-control-sm" style="width: 90px"></td>
          <td class="{{ ($e?->kg_dif ?? 0) < 0 ? 'text-danger' : '' }}">{{ $e?->kg_dif !== null ? number_format($e->kg_dif, 0, ',', '.') : '' }}</td>
          <td><input form="{{ $form }}" name="nro_remito" value="{{ $e?->nro_remito }}" class="form-control form-control-sm" style="width: 90px" maxlength="50"></td>
          <td class="text-center"><input form="{{ $form }}" type="checkbox" name="descarga_completa" value="1" @checked($e?->descarga_completa)></td>
          <td class="text-center"><input form="{{ $form }}" type="checkbox" name="certificado_recibido" value="1" @checked($e?->certificado_recibido)></td>
          <td><input form="{{ $form }}" name="observaciones" value="{{ $e?->observaciones }}" class="form-control form-control-sm" maxlength="1000"></td>
          <td>
            <form id="{{ $form }}" method="POST" action="{{ route('expedicion_control.update', $l) }}">
              @csrf @method('PUT')
              <input type="hidden" name="fecha" value="{{ $fecha->toDateString() }}">
              <button class="btn btn-xs btn-primary" title="Guardar"><i class="fas fa-save"></i></button>
            </form>
          </td>
        </tr>
        @empty
        <tr><td colspan="17" class="text-center text-muted">Sin entregas para este día.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endforeach
@endsection
