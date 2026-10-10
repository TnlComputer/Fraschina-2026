@extends('adminlte::page')

@section('title', 'Avisos')

@section('content_header')
<h1>Avisos <small class="text-muted">destinatarios de mails y WhatsApp</small></h1>
@endsection

@section('content')
<div class="alert alert-info small">
  Los mails salen desde <strong>{{ config('expedicion.mail_remitente') }}</strong>.
  Los WhatsApp quedan registrados en el log hasta conectar el proveedor (WhatsApp Business API o el "móvil 2").
  Un destinatario sin molino ni cliente recibe los avisos de todos los pedidos.
  Consultas automáticas: mañana {{ config('expedicion.consulta_manana') }} (entregas del día) y tarde {{ config('expedicion.consulta_tarde') }} (cargas del día siguiente).
</div>

<div class="card">
  <div class="card-header"><h3 class="card-title">Agregar destinatario</h3></div>
  <div class="card-body">
    <form method="POST" action="{{ route('expedicion_avisos.store') }}" class="form-row align-items-end">
      @csrf
      <div class="col-md-2">
        <label class="small mb-0">Molino</label>
        <select name="expedicion_molino_id" class="form-control form-control-sm">
          <option value="">Todos</option>
          @foreach ($molinos as $m)
          <option value="{{ $m->id }}" @selected(old('expedicion_molino_id') == $m->id)>{{ $m->sigla }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label class="small mb-0">Cliente</label>
        <select name="expedicion_cliente_id" class="form-control form-control-sm">
          <option value="">Todos</option>
          @foreach ($clientes as $c)
          <option value="{{ $c->id }}" @selected(old('expedicion_cliente_id') == $c->id)>{{ $c->sigla }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-1">
        <label class="small mb-0">Canal</label>
        <select name="canal" class="form-control form-control-sm">
          @foreach ($canales as $clave => $nombre)
          <option value="{{ $clave }}" @selected(old('canal') === $clave)>{{ $nombre }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label class="small mb-0">Mail o número</label>
        <input type="text" name="destino" value="{{ old('destino') }}" class="form-control form-control-sm" required maxlength="150">
      </div>
      <div class="col-md-3">
        <label class="small mb-0">Eventos</label>
        @foreach ($eventos as $clave => $nombre)
        <div class="form-check">
          <input type="checkbox" name="eventos[]" value="{{ $clave }}" id="ev-{{ $clave }}" class="form-check-input"
            @checked(in_array($clave, old('eventos', ['pedido_nuevo', 'pedido_modificado'])))>
          <label for="ev-{{ $clave }}" class="form-check-label small">{{ $nombre }}</label>
        </div>
        @endforeach
      </div>
      <div class="col-md-1">
        <button class="btn btn-sm btn-primary">Agregar</button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <table class="table table-sm table-striped mb-0">
      <thead class="thead-light"><tr><th>Molino</th><th>Cliente</th><th>Canal</th><th>Destino</th><th>Evento</th><th>Estado</th><th></th></tr></thead>
      <tbody>
        @forelse ($destinatarios as $d)
        <tr class="{{ $d->activo ? '' : 'text-muted' }}">
          <td>{{ $d->molino?->sigla ?? 'Todos' }}</td>
          <td>{{ $d->cliente?->sigla ?? 'Todos' }}</td>
          <td>{{ $canales[$d->canal] ?? $d->canal }}</td>
          <td>{{ $d->destino }}</td>
          <td>{{ $eventos[$d->evento] ?? $d->evento }}</td>
          <td>
            <form method="POST" action="{{ route('expedicion_avisos.update', $d) }}" class="d-inline">
              @csrf @method('PATCH')
              <button class="btn btn-xs {{ $d->activo ? 'btn-success' : 'btn-outline-secondary' }}">{{ $d->activo ? 'Activo' : 'Inactivo' }}</button>
            </form>
          </td>
          <td>
            <form method="POST" action="{{ route('expedicion_avisos.destroy', $d) }}" class="d-inline" onsubmit="return confirm('¿Eliminar destinatario?');">
              @csrf @method('DELETE')
              <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
            </form>
          </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center text-muted">No hay destinatarios configurados.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
