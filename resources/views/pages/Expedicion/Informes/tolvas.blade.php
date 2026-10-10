@extends('adminlte::page')

@section('title', 'Tolvas y bolsas por molino')

@section('content_header')
<h1>Tolvas y bolsas entregadas por molino</h1>
@endsection

@section('content')
<div class="card">
  <div class="card-body">
    <form method="GET" class="form-inline mb-3">
      <label class="mr-1">Desde</label>
      <input type="date" name="desde" value="{{ $desde }}" class="form-control form-control-sm mr-2">
      <label class="mr-1">Hasta</label>
      <input type="date" name="hasta" value="{{ $hasta }}" class="form-control form-control-sm mr-2">
      <button class="btn btn-sm btn-secondary">Ver</button>
    </form>

    <table class="table table-sm table-bordered" style="max-width: 600px">
      <thead class="thead-light"><tr><th>Molino</th><th class="text-right">Entregas</th><th class="text-right">Tolvas</th><th class="text-right">Bolsas</th></tr></thead>
      <tbody>
        @forelse ($filas as $f)
        <tr>
          <td>{{ $f->sigla }}</td>
          <td class="text-right">{{ $f->entregas }}</td>
          <td class="text-right">{{ number_format($f->tolvas, 0, ',', '.') }}</td>
          <td class="text-right">{{ number_format($f->bolsas, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr><td colspan="4" class="text-center text-muted">Sin entregas en el período.</td></tr>
        @endforelse
      </tbody>
      <tfoot>
        <tr class="font-weight-bold">
          <td>Total</td>
          <td class="text-right">{{ $filas->sum('entregas') }}</td>
          <td class="text-right">{{ number_format($filas->sum('tolvas'), 0, ',', '.') }}</td>
          <td class="text-right">{{ number_format($filas->sum('bolsas'), 0, ',', '.') }}</td>
        </tr>
      </tfoot>
    </table>
    <p class="small text-muted">Incluye pedidos confirmados, pendientes de cierre y cerrados (revisión vigente) con fecha de entrega en el período.</p>
  </div>
</div>
@endsection
