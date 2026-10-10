{{-- @extends('adminlte::page')

@section('content')
<h2>Editar Rol</h2>
<form action="{{ route('roles.update', $role->id) }}" method="POST">
  @csrf
  @method('PUT')
  <div class="form-group">
    <label for="name">Nombre del Rol</label>
    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $role->name) }}" required>
  </div>
  <div class="form-group">
    <label for="permissions">Permisos</label>
    <select name="permissions[]" id="permissions" class="form-control" multiple>
      @foreach ($permissions as $permission)
      <option value="{{ $permission->id }}" {{ $role->permissions->contains($permission->id) ? 'selected' : '' }}>
        {{ $permission->name }}
      </option>
      @endforeach
    </select>
  </div>
  @if ($errors->any())
  <div class="alert alert-danger">
    <ul>
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif
  <button type="submit" class="btn btn-primary">Actualizar Rol</button>
</form>
@endsection --}}


@extends('adminlte::page')

@section('content')
<h2>Editar Rol</h2>
<form action="{{ route('roles.update', $role->id) }}" method="POST">
  @csrf
  @method('PUT')

  <div class="form-group">
    <label for="name">Nombre del Rol</label>
    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $role->name) }}" required>
  </div>

  <div class="form-group">
    <label>Permisos</label>
    <div class="row">
      @foreach ($permissions as $permission)
      <div class="col-md-4">
        <div class="form-check">
          <input type="checkbox" class="form-check-input" id="permission-{{ $permission->id }}" name="permissions[]"
            value="{{ $permission->id }}" {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}>
          <label class="form-check-label" for="permission-{{ $permission->id }}">
            {{ $permission->description }}
          </label>
        </div>
      </div>
      @endforeach
    </div>
  </div>

  @if ($errors->any())
  <div class="alert alert-danger">
    <ul>
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  <div class="form-acciones mt-3">
    <button type="submit" class="btn btn-primary">Actualizar Rol</button>
    <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Cancelar</a>
  </div>
</form>
@endsection