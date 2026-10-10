@extends('adminlte::page')

@section('title', 'Molinos de Expedición')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
  <h1>Molinos de Expedición</h1>
  <a href="{{ route('expedicion_molino.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo</a>
</div>
@endsection

@section('content')
<div class="card">
  <div class="card-body">
    <form method="GET" class="form-inline mb-3">
      <input type="text" name="name" value="{{ $name }}" placeholder="Buscar por sigla o razón social" class="form-control form-control-sm mr-2" style="min-width: 280px">
      <button class="btn btn-sm btn-secondary">Buscar</button>
    </form>

    <table class="table table-sm table-striped table-bordered">
      <thead class="thead-light"><tr><th>Sigla</th><th>Molino</th><th>Contacto (Att.)</th><th>Muestra precio</th><th>Últ. pedido (sistema anterior)</th><th></th></tr></thead>
      <tbody>
        @forelse ($expedicion_molinos as $m)
        <tr>
          <td><strong>{{ $m->sigla }}</strong></td>
          <td>{{ $m->molino?->razonsocial ?? '—' }}</td>
          <td>{{ $m->contMolino }}</td>
          <td>{{ $m->muestraPrecio() ? 'Sí' : 'No' }}</td>
          <td>{{ $m->nroUPed }}</td>
          <td class="text-nowrap">
            <a href="{{ route('expedicion_molino.edit', $m) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
            <a href="{{ route('expedicion_avisos.index') }}" class="btn btn-xs btn-outline-secondary">Avisos</a>
          </td>
        </tr>
        @empty
        <tr><td colspan="6" class="text-center text-muted">Sin molinos.</td></tr>
        @endforelse
      </tbody>
    </table>
    {{ $expedicion_molinos->links() }}
  </div>
</div>
@endsection
