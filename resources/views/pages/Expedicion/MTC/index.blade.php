@extends('adminlte::page')

@section('title', 'Configuración cliente-molino')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
  <h1>Configuración cliente-molino</h1>
  @can('permiso_11')<a href="{{ route('expedicion_MTC.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nueva</a>@endcan
</div>
@endsection

@section('content')
<div class="card">
  <div class="card-body">
    <form method="GET" class="form-inline mb-3">
      <select name="cliente_id" class="form-control form-control-sm mr-2">
        <option value="">Todos los clientes</option>
        @foreach ($clientes as $c)
        <option value="{{ $c->id }}" @selected(request('cliente_id') == $c->id)>{{ $c->nombre }}</option>
        @endforeach
      </select>
      <select name="molino_id" class="form-control form-control-sm mr-2">
        <option value="">Todos los molinos</option>
        @foreach ($molinos as $m)
        <option value="{{ $m->id }}" @selected(request('molino_id') == $m->id)>{{ $m->nombre }}</option>
        @endforeach
      </select>
      <button class="btn btn-sm btn-secondary">Filtrar</button>
    </form>

    <table class="table table-sm table-striped table-bordered">
      <thead class="thead-light">
        <tr><th>Cliente</th><th>Molino</th><th>Plazo</th><th>Forma de pago</th><th>Condición</th><th>Planta</th><th>Planilla</th><th></th></tr>
      </thead>
      <tbody>
        @forelse ($configuraciones as $cfg)
        <tr>
          <td>{{ $cfg->cliente?->sigla }}</td>
          <td>{{ $cfg->molino?->sigla }}</td>
          <td>{{ $cfg->plazo_dias !== null ? $cfg->plazo_dias . ' días' : '' }}</td>
          <td>{{ $cfg->forma_pago }}</td>
          <td>{{ $cfg->condicion }}</td>
          <td>{{ $cfg->planta }}</td>
          <td class="small">
            {{ $cfg->mostrar_precio === null ? 'Precio según molino' : ($cfg->mostrar_precio ? 'Precio' : 'Sin precio') }}{{ $cfg->subrayar_precio ? ' subrayado' : '' }}
            · {{ $cfg->mostrar_fecha_alternativa ? 'Fecha alt.' : 'Sin fecha alt.' }}
            · {{ $cfg->mostrar_especiales ? 'Especiales' : 'Sin especiales' }}
          </td>
          <td class="text-nowrap">
            <a href="{{ route('expedicion_MTC.edit', $cfg) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
            <form method="POST" action="{{ route('expedicion_MTC.destroy', $cfg) }}" class="d-inline" onsubmit="return confirm('¿Eliminar configuración?');">
              @csrf @method('DELETE')
              <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
            </form>
          </td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-center text-muted">Sin configuraciones.</td></tr>
        @endforelse
      </tbody>
    </table>
    {{ $configuraciones->links() }}
  </div>
</div>
@endsection
