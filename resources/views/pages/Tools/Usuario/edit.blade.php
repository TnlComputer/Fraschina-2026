@extends('adminlte::page')

@section('title', 'Editar Usuario')

@section('content')
<div class="container">
  <h2>Editar Usuario</h2>

  <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="form-group">
      <label for="name">Nombre</label>
      <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $usuario->name) }}" required>
    </div>

    <div class="form-group">
      <label for="email">Correo Electrónico</label>
      <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $usuario->email) }}"
        required>
    </div>

    {{-- La contraseña solo se toca si se pide: así el navegador no la autocompleta al cambiar el rol --}}
    <div class="custom-control custom-checkbox mb-3">
      <input type="checkbox" class="custom-control-input" name="cambiar_password" id="cambiar_password" value="1" @checked(old('cambiar_password'))>
      <label class="custom-control-label font-weight-normal" for="cambiar_password">Cambiar contraseña</label>
    </div>

    <div id="grupo-password" style="display: none">
      <div class="form-group">
        <label for="password">Nueva contraseña</label>
        <input type="password" name="password" id="password" class="form-control" autocomplete="new-password" disabled>
      </div>

      <div class="form-group">
        <label for="password_confirmation">Confirmar contraseña</label>
        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" autocomplete="new-password" disabled>
      </div>
    </div>

    <!-- Roles -->
    <div class="form-group">
      <label for="role">Rol</label>
      <select name="role" id="role" class="form-control">
        <option value="">Seleccione un rol</option>
        @foreach ($roles as $role)
        <option value="{{ $role->id }}" {{ old('role', $usuario->roles->first()->id ?? '') == $role->id ? 'selected' :
          '' }}>
          {{ $role->name }}
        </option>
        @endforeach
      </select>
    </div>

    <!-- Campo para is_active -->
    <div class="form-group">
      <label for="is_active">Activo</label>
      @if ($usuario->is(auth()->user()))
      {{-- Nadie se desactiva a sí mismo --}}
      <input type="hidden" name="is_active" value="1">
      <input type="checkbox" id="is_active" checked disabled>
      <small class="text-muted ml-1">No puede desactivar su propio usuario.</small>
      @else
      <input type="hidden" name="is_active" value="0"> <!-- Campo oculto para el valor falso -->
      <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $usuario->is_active) ?
      'checked' : '' }}>
      @endif
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

    <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
  </form>
</div>
@endsection

@push('js')
<script>
  (function () {
    var casilla = document.getElementById('cambiar_password');
    var grupo = document.getElementById('grupo-password');
    var actualizar = function () {
      grupo.style.display = casilla.checked ? '' : 'none';
      grupo.querySelectorAll('input').forEach(function (i) {
        i.disabled = !casilla.checked;
        i.required = casilla.checked;
        if (!casilla.checked) i.value = '';
      });
    };
    casilla.addEventListener('change', actualizar);
    actualizar();
  })();
</script>
@endpush