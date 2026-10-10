@extends('adminlte::page')

@section('title', 'Auditoría')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
  <h1>Auditoría <small class="text-muted">operaciones por usuario</small></h1>
  <a href="{{ route('auditoria.export', request()->query()) }}" class="btn btn-outline-success btn-sm"><i class="fas fa-file-excel"></i> Exportar a Excel</a>
</div>
@endsection

@section('content')
@php
$colores = ['alta' => 'success', 'modificacion' => 'info', 'eliminacion' => 'danger', 'restauracion' => 'warning'];
@endphp
<div class="card">
  <div class="card-body">
    <form method="GET" class="form-row align-items-end mb-3">
      <div class="col-md-2">
        <label class="small mb-0">Usuario</label>
        <select name="user_id" class="form-control form-control-sm">
          <option value="">Todos</option>
          @foreach ($usuarios as $u)
          <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label class="small mb-0">Sector</label>
        <select name="sector" class="form-control form-control-sm">
          <option value="">Todos</option>
          @foreach ($sectores as $s)
          <option value="{{ $s }}" @selected(request('sector') === $s)>{{ $s }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label class="small mb-0">Acción</label>
        <select name="accion" class="form-control form-control-sm">
          <option value="">Todas</option>
          @foreach ($acciones as $clave => $nombre)
          <option value="{{ $clave }}" @selected(request('accion') === $clave)>{{ $nombre }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-1">
        <label class="small mb-0">Desde</label>
        <input type="date" name="desde" value="{{ request('desde') }}" class="form-control form-control-sm">
      </div>
      <div class="col-md-1">
        <label class="small mb-0">Hasta</label>
        <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control form-control-sm">
      </div>
      <div class="col-md-2">
        <label class="small mb-0">Buscar</label>
        <input type="text" name="buscar" value="{{ request('buscar') }}" class="form-control form-control-sm" placeholder="Registro, texto, valor...">
      </div>
      <div class="col-md-2">
        <button class="btn btn-sm btn-secondary">Filtrar</button>
        <a href="{{ route('auditoria.index') }}" class="btn btn-sm btn-link">Limpiar</a>
      </div>
    </form>

    <table class="table table-sm table-striped">
      <thead class="thead-light">
        <tr><th>Fecha y hora</th><th>Usuario</th><th>Sector</th><th>Acción</th><th>Registro</th><th>Cambios</th></tr>
      </thead>
      <tbody>
        @forelse ($registros as $r)
        <tr>
          <td class="text-nowrap small">{{ $r->created_at?->format('d/m/Y H:i:s') }}</td>
          <td class="small">{{ $r->usuario ?? '—' }}</td>
          <td class="small">{{ $r->sector }}</td>
          <td><span class="badge badge-{{ $colores[$r->accion] ?? 'light' }}">{{ $r->accion_nombre }}</span></td>
          <td class="small">{{ $r->descripcion }}<div class="text-muted">{{ $r->tabla }} #{{ $r->registro_id }}</div></td>
          <td class="small">
            @if ($r->cambios)
            @php $cambios = collect($r->cambios); @endphp
            <details>
              <summary>{{ $cambios->count() }} {{ $cambios->count() === 1 ? 'campo' : 'campos' }}</summary>
              <table class="table table-sm table-bordered mb-0 mt-1">
                <thead><tr><th>Campo</th><th>Antes</th><th>Después</th></tr></thead>
                <tbody>
                  @foreach ($cambios as $campo => $valores)
                  <tr>
                    <td>{{ $campo }}</td>
                    <td class="text-danger">{{ \Illuminate\Support\Str::limit((string) ($valores[0] ?? ''), 120) }}</td>
                    <td class="text-success">{{ \Illuminate\Support\Str::limit((string) ($valores[1] ?? ''), 120) }}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </details>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="6" class="text-center text-muted">Sin operaciones con esos filtros.</td></tr>
        @endforelse
      </tbody>
    </table>

    {{ $registros->links() }}
  </div>
</div>
@endsection
