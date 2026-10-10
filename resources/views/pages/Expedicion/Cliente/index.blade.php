@extends('adminlte::page')

@section('title', 'Clientes de Expedición')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
  <h1>Clientes de Expedición</h1>
  <a href="{{ route('expedicion_cliente.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo</a>
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
      <thead class="thead-light"><tr><th>Sigla</th><th>Cliente</th><th>Textos de la planilla</th><th></th></tr></thead>
      <tbody>
        @forelse ($clientes as $c)
        <tr>
          <td><strong>{{ $c->sigla }}</strong></td>
          <td>{{ $c->representacion?->razonsocial ?? '—' }}</td>
          <td class="small">{{ \Illuminate\Support\Str::limit(implode(' / ', $c->lineasTexto()), 120) }}</td>
          <td class="text-nowrap">
            <a href="{{ route('expedicion_cliente.edit', $c) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
            <a href="{{ route('expedicion_MTC.index', ['cliente_id' => $c->id]) }}" class="btn btn-xs btn-outline-secondary" title="Configuración por molino">Molinos</a>
          </td>
        </tr>
        @empty
        <tr><td colspan="4" class="text-center text-muted">Sin clientes.</td></tr>
        @endforelse
      </tbody>
    </table>
    {{ $clientes->links() }}
  </div>
</div>
@endsection
