@extends('adminlte::page')

@section('title', 'Dudas de migración')

@section('content_header')
<div class="d-flex flex-wrap align-items-center justify-content-between">
  <h1>Dudas de migración</h1>
  <button type="button" class="btn btn-outline-secondary d-print-none" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
</div>
@endsection

@section('content')
<p class="text-muted mb-2">Dudas que surgieron al pasar las planillas (Ctrl, Diaria, General, Plan ped y Fichas) al sistema. Consultalas con el cliente y anotá la respuesta.</p>

<div class="btn-group mb-3 d-print-none">
  @foreach (['pendientes' => "Pendientes ($pendientes)", 'resueltas' => "Resueltas ($resueltas)", 'todas' => 'Todas'] as $valor => $texto)
  <a href="{{ route('expedicion_dudas.index', ['estado' => $valor]) }}" class="btn btn-sm {{ $estado === $valor ? 'btn-secondary' : 'btn-outline-secondary' }}">{{ $texto }}</a>
  @endforeach
</div>

@forelse ($dudas as $n => $duda)
@php $filas = $duda->detalle['filas'] ?? []; @endphp
<div class="card {{ $duda->resuelta_at ? 'card-outline card-success' : 'card-outline card-warning' }}" id="duda-{{ $duda->id }}">
  <div class="card-header">
    <h3 class="card-title font-weight-bold">{{ $n + 1 }}. {{ $duda->tema }}</h3>
    <div class="card-tools">
      <span class="badge badge-light border">{{ $duda->origen }}</span>
      @if ($duda->resuelta_at)<span class="badge badge-success">Resuelta</span>@endif
    </div>
  </div>
  <div class="card-body">
    <p class="mb-2">{{ $duda->pregunta }}</p>

    @if ($filas)
    <details @if (count($filas) <= 15) open @endif>
      <summary class="text-primary mb-2" style="cursor: pointer">Ver detalle ({{ count($filas) }} {{ count($filas) === 1 ? 'fila' : 'filas' }})</summary>
      <div class="table-responsive" style="max-height: 420px">
        <table class="table table-sm table-bordered mb-0">
          <thead><tr>@foreach ($duda->detalle['columnas'] as $col)<th>{{ $col }}</th>@endforeach</tr></thead>
          <tbody>
            @foreach ($filas as $fila)
            <tr>@foreach ($fila as $valor)<td class="small">{{ is_array($valor) ? json_encode($valor, JSON_UNESCAPED_UNICODE) : $valor }}</td>@endforeach</tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </details>
    @endif

    <form method="POST" action="{{ route('expedicion_dudas.update', $duda) }}" class="mt-3">
      @csrf
      @method('PUT')
      <label for="respuesta-{{ $duda->id }}" class="mb-1">Respuesta del cliente</label>
      <textarea name="respuesta" id="respuesta-{{ $duda->id }}" rows="2" class="form-control" maxlength="5000"
        placeholder="Anotar acá lo que conteste el cliente" @cannot('permiso_12') readonly @endcannot>{{ old('respuesta', $duda->respuesta) }}</textarea>
      @can('permiso_12')
      <div class="form-acciones mt-2 d-print-none">
        <input type="hidden" name="resuelta" value="0">
        <div class="custom-control custom-checkbox mr-3">
          <input type="checkbox" class="custom-control-input" id="resuelta-{{ $duda->id }}" name="resuelta" value="1" @checked($duda->resuelta_at)>
          <label class="custom-control-label font-weight-normal" for="resuelta-{{ $duda->id }}">Resuelta</label>
        </div>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Guardar</button>
      </div>
      @endcan
      @if ($duda->resuelta_at)
      <small class="text-muted">Resuelta el {{ fecha($duda->resuelta_at, 'd/m/Y H:i') }}{{ $duda->resolvio ? ' por ' . $duda->resolvio->name : '' }}.</small>
      @endif
    </form>
  </div>
</div>
@empty
<div class="card"><div class="card-body text-center text-muted py-4">
  {{ $estado === 'pendientes' ? 'No hay dudas pendientes.' : 'No hay dudas para mostrar.' }}
</div></div>
@endforelse
@endsection

@push('css')
<style>
  @media print {
    details { display: block; }
    details > summary { display: none; }
    details > div { max-height: none !important; }
    textarea { border: 1px solid #999; min-height: 4em; }
  }
</style>
@endpush
