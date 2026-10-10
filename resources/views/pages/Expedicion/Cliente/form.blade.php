@extends('adminlte::page')

@section('title', $cliente->exists ? 'Editar cliente' : 'Nuevo cliente')

@section('content_header')
<h1>{{ $cliente->exists ? 'Editar cliente ' . $cliente->sigla : 'Nuevo cliente de Expedición' }}</h1>
@endsection

@section('content')
<form method="POST" action="{{ $cliente->exists ? route('expedicion_cliente.update', $cliente) : route('expedicion_cliente.store') }}">
  @csrf
  @if ($cliente->exists) @method('PUT') @endif
  <div class="card">
    <div class="card-body">
      <div class="form-row">
        <div class="col-md-2">
          <label>Sigla *</label>
          <input type="text" name="sigla" value="{{ old('sigla', $cliente->sigla) }}" class="form-control" maxlength="5" required>
        </div>
        <div class="col-md-6">
          <label>Cliente (Representación)</label>
          <select name="representacion_id" class="form-control">
            <option value="">Sin vincular</option>
            @foreach ($representaciones as $r)
            <option value="{{ $r->id }}" @selected(old('representacion_id', $cliente->representacion_id) == $r->id)>{{ $r->razonsocial }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <label class="mt-3">Textos al pie de la planilla de pedido</label>
      <p class="small text-muted mb-1">Se usan para todos los molinos, salvo que la combinación cliente-molino tenga textos propios.</p>
      @foreach (range(1, 6) as $i)
      <input type="text" name="linea{{ $i }}" value="{{ old('linea' . $i, $cliente->{'linea' . $i}) }}" class="form-control form-control-sm mb-1" maxlength="200" placeholder="Línea {{ $i }}">
      @endforeach
      <div class="form-row mt-3">
        <div class="col-md-6">
          <label>Observaciones</label>
          <textarea name="obs" rows="2" class="form-control">{{ old('obs', $cliente->obs) }}</textarea>
        </div>
        <div class="col-md-6">
          <label>Comentarios</label>
          <textarea name="comentarios" rows="2" class="form-control">{{ old('comentarios', $cliente->comentarios) }}</textarea>
        </div>
      </div>
    </div>
    <div class="card-footer">
      <button class="btn btn-success">Guardar</button>
      <a href="{{ route('expedicion_cliente.index') }}" class="btn btn-secondary">Cancelar</a>
    </div>
  </div>
</form>
@endsection
