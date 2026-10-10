@extends('adminlte::page')

@section('title', $molino->exists ? 'Editar molino' : 'Nuevo molino')

@section('content_header')
<h1>{{ $molino->exists ? 'Editar molino ' . $molino->sigla : 'Nuevo molino de Expedición' }}</h1>
@endsection

@section('content')
<form method="POST" action="{{ $molino->exists ? route('expedicion_molino.update', $molino) : route('expedicion_molino.store') }}">
  @csrf
  @if ($molino->exists) @method('PUT') @endif
  <div class="card">
    <div class="card-body">
      <div class="form-row">
        <div class="col-md-2">
          <label>Sigla *</label>
          <input type="text" name="sigla" value="{{ old('sigla', $molino->sigla) }}" class="form-control" maxlength="5" required>
        </div>
        <div class="col-md-5">
          <label>Molino</label>
          <select name="nroMolino_id" class="form-control">
            <option value="">Sin vincular</option>
            @foreach ($molinos as $m)
            <option value="{{ $m->id }}" @selected(old('nroMolino_id', $molino->nroMolino_id) == $m->id)>{{ $m->razonsocial }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-5">
          <label>Contacto (Att. en la planilla)</label>
          <input type="text" name="contMolino" value="{{ old('contMolino', $molino->contMolino) }}" class="form-control" maxlength="255">
        </div>
      </div>
      <div class="form-check mt-3">
        <input type="checkbox" name="calificacion" value="1" id="calificacion" class="form-check-input" @checked(old('calificacion', $molino->exists ? $molino->muestraPrecio() : true))>
        <label for="calificacion" class="form-check-label">Mostrar el precio en la planilla de pedido</label>
        <small class="form-text text-muted">Se puede ajustar por cliente en la configuración cliente-molino.</small>
      </div>
    </div>
    <div class="card-footer form-acciones">
      <button class="btn btn-primary">Guardar</button>
      <a href="{{ route('expedicion_molino.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
  </div>
</form>
@endsection
