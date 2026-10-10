{{-- @extends('adminlte::page')

@section('content')
<h2>Crear Nuevo Rol</h2>
<form action="{{ route('roles.store') }}" method="POST">
  @csrf
  <div class="form-group">
    <label for="name">Nombre del Rol</label>
    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
  </div>
  <div class="form-group">
    <label for="permissions">Permisos</label>
    <select name="permissions[]" id="permissions" class="form-control" multiple>
      @foreach ($permissions as $permission)
      <option value="{{ $permission->id }}">{{ $permission->name }}</option>
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
  <button type="submit" class="btn btn-primary">Crear Rol</button>
</form>
@endsection --}}


@extends('adminlte::page')

@section('content')
<h2>Crear Nuevo Rol</h2>
<form action="{{ route('roles.store') }}" method="POST">
  @csrf

  <div class="form-group">
    <label for="name">Nombre del Rol</label>
    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
  </div>

  <div class="form-group">
    <label>Permisos</label>
    <div class="row">
      @foreach ($permissions as $permission)
      <div class="col-md-4">
        <div class="form-check">
          <input type="checkbox" class="form-check-input" id="permission-{{ $permission->id }}" name="permissions[]"
            value="{{ $permission->id }}">
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
    <button type="submit" class="btn btn-primary">Crear Rol</button>
    <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Cancelar</a>
  </div>
</form>
@endsection