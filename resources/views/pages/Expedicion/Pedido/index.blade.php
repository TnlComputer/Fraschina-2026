@extends('adminlte::page')

@section('title', 'Pedidos')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
  <h1>Pedidos</h1>
  @can('permiso_11')<a href="{{ route('expedicion_pedido.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo pedido</a>@endcan
</div>
@endsection

@section('content')
<div class="card">
  <div class="card-body">
    <form method="GET" action="{{ route('expedicion_pedido.index') }}" class="form-row align-items-end mb-3">
      <div class="col-md-1">
        <label class="small mb-0">Número</label>
        <input type="number" name="numero" value="{{ request('numero') }}" class="form-control form-control-sm">
      </div>
      <div class="col-md-2">
        <label class="small mb-0">Estado</label>
        <select name="estado" class="form-control form-control-sm">
          <option value="">Todos</option>
          @foreach ($estados as $clave => $nombre)
          <option value="{{ $clave }}" @selected(request('estado') === $clave)>{{ $nombre }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label class="small mb-0">Molino</label>
        <select name="molino_id" class="form-control form-control-sm">
          <option value="">Todos</option>
          @foreach ($molinos as $m)
          <option value="{{ $m->id }}" @selected(request('molino_id') == $m->id)>{{ $m->nombre }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label class="small mb-0">Cliente</label>
        <select name="cliente_id" class="form-control form-control-sm">
          <option value="">Todos</option>
          @foreach ($clientes as $c)
          <option value="{{ $c->id }}" @selected(request('cliente_id') == $c->id)>{{ $c->nombre }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-1">
        <label class="small mb-0">Desde</label>
        <input type="date" name="desde" data-dias="exp_pedidos" value="{{ request('desde') }}" class="form-control form-control-sm">
      </div>
      <div class="col-md-1">
        <label class="small mb-0">Hasta</label>
        <input type="date" name="hasta" data-dias="exp_pedidos" value="{{ request('hasta') }}" class="form-control form-control-sm">
      </div>
      <div class="col-md-1">
        <div class="form-check">
          <input type="checkbox" name="historico" value="1" id="historico" class="form-check-input" @checked(request('historico'))>
          <label for="historico" class="form-check-label small">Revisiones viejas</label>
        </div>
      </div>
      <div class="col-md-2">
        <button class="btn btn-secondary btn-sm">Filtrar</button>
        <a href="{{ route('expedicion_pedido.index') }}" class="btn btn-link btn-sm">Limpiar</a>
      </div>
    </form>

    <table class="table table-sm table-striped table-bordered">
      <thead class="thead-light">
        <tr>
          <th>Pedido</th><th>Fecha</th><th>Estado</th><th>Molino</th><th>Cliente</th>
          <th>Calidades</th><th class="text-center">Tolvas</th><th class="text-center">Bolsas</th><th>Entregas</th><th>Enviado</th><th></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($pedidos as $p)
        <tr class="{{ !$p->vigente ? 'text-muted' : '' }}">
          <td>
            <a href="{{ route('expedicion_pedido.show', $p) }}"><strong>{{ $p->codigo }}</strong></a>
            @if ($p->tipo === 'muestra') <span class="badge badge-secondary">Muestra</span> @endif
            @if (!$p->vigente) <span class="badge badge-light">reemplazado</span> @endif
          </td>
          <td>{{ $p->fecha_pedido?->format('d/m/Y') }}</td>
          <td>@include('pages.Expedicion.partials.estado', ['pedido' => $p])</td>
          <td>{{ $p->molino->sigla }}</td>
          <td>{{ $p->cliente->sigla }}</td>
          <td class="small">{{ $p->lineas->pluck('calidad')->unique()->implode(', ') }}</td>
          <td class="text-center">{{ $p->totalTolvas() ?: '' }}</td>
          <td class="text-center">{{ $p->totalBolsas() ?: '' }}</td>
          <td class="small">
            @foreach ($p->lineas as $l)
            <span class="{{ $l->fecha_tentativa ? 'text-primary' : '' }}">{{ $l->fecha_entrega?->format('d/m') ?? 'a conf.' }}</span>@if (!$loop->last), @endif
            @endforeach
          </td>
          <td class="small">{{ $p->enviado_at?->format('d/m H:i') }}</td>
          <td class="text-nowrap">
            <a href="{{ route('expedicion_pedido.show', $p) }}" class="btn btn-xs btn-info" title="Ver"><i class="fas fa-eye"></i></a>
            @if ($p->esEditable())
            <a href="{{ route('expedicion_pedido.edit', $p) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="11" class="text-center text-muted">No hay pedidos con esos filtros.</td></tr>
        @endforelse
      </tbody>
    </table>

    {{ $pedidos->links() }}
  </div>
</div>
@endsection

@include('partials.calendario-dias')
