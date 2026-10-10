@extends('adminlte::page')

@php
$editando = $role->exists;
$titulo = $editando ? "Editar rol «{$role->name}»" : 'Nuevo rol';
$elegidos = collect(old('permissions', $role->permissions?->pluck('id')->all() ?? []))->map(fn($id) => (int) $id);
@endphp

@section('title', $titulo)

@section('content_header')
<h1>{{ $titulo }}</h1>
@endsection

@section('content')
<form method="POST" action="{{ $editando ? route('roles.update', $role) : route('roles.store') }}">
  @csrf
  @if ($editando) @method('PUT') @endif

  <div class="card" style="max-width: 900px">
    <div class="card-body">
      @if ($esSuper)
      <div class="alert alert-warning py-2">
        <i class="fas fa-shield-alt"></i> Es el rol del <strong>Super Usuario</strong>: tiene acceso total, tilde lo que tilde.
      </div>
      @endif

      <div class="form-group" style="max-width: 400px">
        <label for="name">Nombre del rol <span class="text-danger">*</span></label>
        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
          value="{{ old('name', $role->name) }}" maxlength="100" required autofocus>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      @foreach ([['Secciones', 'A qué pantallas entra', $secciones, 'fa-th-large'], ['Acciones', 'Qué puede hacer con los registros de esas secciones', $acciones, 'fa-tasks']] as [$grupo, $ayuda, $lista, $icono])
      <div class="grupo-permisos mt-3" data-grupo="{{ $grupo }}">
        <div class="d-flex flex-wrap align-items-baseline justify-content-between border-bottom mb-2">
          <h5 class="form-seccion border-0 mb-1"><i class="fas {{ $icono }}"></i> {{ $grupo }}
            <small class="text-muted font-weight-normal ml-1">{{ $ayuda }}</small></h5>
          <div class="small">
            <a href="#" class="marcar" data-valor="1">Todas</a> · <a href="#" class="marcar" data-valor="0">Ninguna</a>
          </div>
        </div>
        <div class="row">
          @foreach ($lista as $permiso)
          <div class="col-6 col-md-4">
            <div class="custom-control custom-checkbox mb-2">
              <input type="checkbox" class="custom-control-input" id="permiso-{{ $permiso->id }}" name="permissions[]"
                value="{{ $permiso->id }}" @checked($elegidos->contains($permiso->id))>
              <label class="custom-control-label font-weight-normal" for="permiso-{{ $permiso->id }}">{{ $permiso->description }}</label>
            </div>
          </div>
          @endforeach
        </div>
      </div>
      @endforeach
      <p class="small text-muted mb-0">Sin «Alta», «Editar» o «Eliminar», el usuario solo consulta.</p>
    </div>
    <div class="card-footer form-acciones">
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
      <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
  </div>
</form>
@endsection

@push('js')
<script>
  document.querySelectorAll('.grupo-permisos .marcar').forEach(function (a) {
    a.addEventListener('click', function (e) {
      e.preventDefault();
      a.closest('.grupo-permisos').querySelectorAll('input[type=checkbox]').forEach(function (c) { c.checked = a.dataset.valor === '1'; });
    });
  });
</script>
@endpush
