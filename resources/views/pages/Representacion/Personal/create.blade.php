@extends('adminlte::page')

@section('title', 'Nuevo Personal')

@section('content_header')
<h1>Nuevo Personal</h1>
@endsection

@section('content')
<div class="card">
  <div class="card-body">
    <form id="personalForm" action="{{ route('representacion_personal.store') }}" method="POST">
      @csrf
      <input type="hidden" name="representacion_id" value="{{ $representacion->id }}">

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror"
              value="{{ old('nombre') }}" required>
            @error('nombre')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="apellido">Apellido</label>
            <input type="text" name="apellido" id="apellido"
              class="form-control @error('apellido') is-invalid @enderror" value="{{ old('apellido') }}" required>
            @error('apellido')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="teldirecto">Teléfono Directo</label>
            <input type="text" name="teldirecto" id="teldirecto"
              class="form-control @error('teldirecto') is-invalid @enderror" value="{{ old('teldirecto') }}">
            @error('teldirecto')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="interno">Interno</label>
            <input type="text" name="interno" id="interno" class="form-control @error('interno') is-invalid @enderror"
              value="{{ old('interno') }}">
            @error('interno')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="telparticular">Teléfono Particular</label>
            <input type="text" name="telparticular" id="telparticular"
              class="form-control @error('telparticular') is-invalid @enderror" value="{{ old('telparticular') }}">
            @error('telparticular')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="telcelular">Celular</label>
            <input type="text" name="telcelular" id="telcelular"
              class="form-control @error('telcelular') is-invalid @enderror" value="{{ old('telcelular') }}">
            @error('telcelular')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label for="profesion_id">Profesión</label>
            <select name="profesion_id" id="profesion_id"
              class="form-control @error('profesion_id') is-invalid @enderror">
              <option value="">Seleccione una opción</option>
              @foreach($profesiones as $profesion)
              <option value="{{ $profesion->id }}" {{ old('profesion_id')==$profesion->id ? 'selected' : '' }}>
                {{ $profesion->nombreprofesion }}
              </option>
              @endforeach
            </select>
            @error('profesion_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="area_id">Área</label>
            <select name="area_id" id="area_id" class="form-control @error('area_id') is-invalid @enderror">
              <option value="">Seleccione una opción</option>
              @foreach($areas as $area)
              <option value="{{ $area->id }}" {{ old('area_id')==$area->id ? 'selected' : '' }}>
                {{ $area->area }}
              </option>
              @endforeach
            </select>
            @error('area_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="cargo_id">Cargo</label>
            <select name="cargo_id" id="cargo_id" class="form-control @error('cargo_id') is-invalid @enderror">
              <option value="">Seleccione una opción</option>
              @foreach($cargos as $cargo)
              <option value="{{ $cargo->id }}" {{ old('cargo_id')==$cargo->id ? 'selected' : '' }}>
                {{ $cargo->cargo }}
              </option>
              @endforeach
            </select>
            @error('cargo_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>

      <div class="form-group">
        <label for="observaciones">Observaciones</label>
        <textarea name="observaciones" id="observaciones"
          class="form-control @error('observaciones') is-invalid @enderror">{{ old('observaciones') }}</textarea>
        @error('observaciones')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-acciones mt-3">
        <button type="submit" id="submitButton" class="btn btn-primary" disabled>Nuevo Personal</button>
        <a href="{{ route('representacion.show', $representacion->id) }}" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('personalForm');
    const submitButton = document.getElementById('submitButton');

    form.addEventListener('change', function () {
      const allRequired = Array.from(form.querySelectorAll('[required]'));
      const allValid = allRequired.every(input => input.value.trim() !== '');
      submitButton.disabled = !allValid;
    });
  });
</script>
@endsection