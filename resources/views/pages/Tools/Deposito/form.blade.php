@extends('adminlte::page')

@php $titulo = $deposito->exists ? 'Editar depósito' : 'Nuevo depósito'; @endphp

@section('title', $titulo)

@section('content_header')
<h1>{{ $titulo }}</h1>
@endsection

@section('content')
<form method="POST" action="{{ $deposito->exists ? route('depositos.update', $deposito->id) : route('depositos.store') }}">
  @csrf
  @if ($deposito->exists) @method('PUT') @endif

  <div class="card" style="max-width: 760px">
    <div class="card-body">
      <div class="form-group">
        <label for="nombre">Nombre <span class="text-danger">*</span></label>
        <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror"
          value="{{ old('nombre', $deposito->nombre) }}" maxlength="100" required autofocus placeholder="Depósito Ciudadela">
        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="form-row">
        <div class="form-group col-md-7">
          <label for="direccion">Dirección <span class="text-danger">*</span></label>
          <input type="text" name="direccion" id="direccion" class="form-control @error('direccion') is-invalid @enderror"
            value="{{ old('direccion', $deposito->direccion) }}" maxlength="150" required placeholder="Calle y número">
          @error('direccion')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-5">
          <label for="localidad">Localidad <span class="text-danger">*</span></label>
          <input type="text" name="localidad" id="localidad" class="form-control @error('localidad') is-invalid @enderror"
            value="{{ old('localidad', $deposito->localidad) }}" maxlength="150" required placeholder="Ciudadela, Tres de Febrero">
          @error('localidad')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="custom-control custom-checkbox mb-3">
        <input type="checkbox" class="custom-control-input" name="principal" id="principal" value="1" @checked(old('principal', $deposito->principal))>
        <label class="custom-control-label font-weight-normal" for="principal">Depósito principal (de acá sale el reparto)</label>
      </div>

      <h5 class="form-seccion mt-3"><i class="fas fa-location-dot"></i> Ubicación en el mapa</h5>
      <div class="form-row">
        <div class="form-group col-6 col-md-3">
          <label for="latitud">Latitud</label>
          <input type="text" inputmode="decimal" name="latitud" id="latitud" class="form-control @error('latitud') is-invalid @enderror"
            value="{{ old('latitud', str_replace('.', ',', (string) $deposito->latitud)) }}" placeholder="-34,638">
          @error('latitud')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-6 col-md-3">
          <label for="longitud">Longitud</label>
          <input type="text" inputmode="decimal" name="longitud" id="longitud" class="form-control @error('longitud') is-invalid @enderror"
            value="{{ old('longitud', str_replace('.', ',', (string) $deposito->longitud)) }}" placeholder="-58,540">
          @error('longitud')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-6 d-flex align-items-end">
          @if ($deposito->tieneUbicacion())
          <a href="{{ $deposito->urlMapa() }}" target="_blank" rel="noopener" class="btn btn-outline-secondary"><i class="fas fa-map-location-dot"></i> Ver en el mapa</a>
          @endif
        </div>
      </div>
      <small class="form-text text-muted mt-n2">Se completan solas al guardar, buscando la dirección. Si el punto no queda bien, se pueden corregir a mano
        (en Google Maps, clic derecho sobre el lugar muestra la latitud y la longitud).</small>

      <div class="form-group mt-3">
        <label for="observaciones">Observaciones</label>
        <textarea name="observaciones" id="observaciones" rows="2" class="form-control @error('observaciones') is-invalid @enderror" maxlength="1000">{{ old('observaciones', $deposito->observaciones) }}</textarea>
        @error('observaciones')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>
    <div class="card-footer form-acciones">
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
      <a href="{{ route('depositos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
  </div>
</form>
@endsection
