@extends('adminlte::page')

@section('title', 'Editar permiso')

@section('content_header')
<h1>Editar permiso</h1>
@endsection

@section('content')
<form method="POST" action="{{ route('permissions.update', $permission) }}">
  @csrf
  @method('PUT')

  <div class="card" style="max-width: 620px">
    <div class="card-body">
      <div class="form-group">
        <label for="description">Nombre <span class="text-danger">*</span></label>
        <input type="text" name="description" id="description" class="form-control @error('description') is-invalid @enderror"
          value="{{ old('description', $permission->description) }}" maxlength="100" required autofocus>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <small class="form-text text-muted">Es el nombre que se ve en Roles y en esta pantalla.</small>
      </div>
      <div class="form-group mb-0">
        <label>Código</label>
        <p class="form-control-plaintext py-0"><code>{{ $permission->name }}</code></p>
        <small class="form-text text-muted">Lo usa el sistema para controlar el acceso; no se modifica.</small>
      </div>
    </div>
    <div class="card-footer form-acciones">
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
      <a href="{{ route('permissions.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
  </div>
</form>
@endsection
