@extends('adminlte::page')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card-header">
        <h3 class="card-title">{{ __('Usuarios') }}</h3>
      </div>
      <div class="col-12">
        <div class="py-2">
          <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-2 text-gray-900 text-left text-xs">
                <div class="barra__index d-flex justify-content-between align-items-center mb-3">
                  <div class="div__nuevo">
                    @can('permiso_11')<form action="{{ route('usuarios.create') }}">
                      <input class="btn btn-primary" type="submit" value="Nuevo">
                    </form>@endcan
                  </div>
                  <div class="div__buscar d-flex">
                    <form method="get" action="{{ route('usuarios.index') }}" class="form__buscar d-flex">
                      @csrf
                      <input type="text" placeholder="Buscar por nombre" name="name" value="{{ $name }}"
                        class="form-control me-2" style="max-width: 350px;">
                      <input type="submit" value="Buscar" class="btn btn-secondary">
                    </form>
                  </div>
                </div>
                <table class="table table-sm table-striped table-bordered w-100 text-md">
                  <thead>
                    <tr>
                      <th>Nombre</th>
                      <th>Email</th>
                      <th>Rol</th>
                      <th class="text-center">Estado</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                  @forelse($usuarios as $usuario)
                  @php $activo = $usuario->status == 'A'; @endphp
                  <tr class="{{ $activo ? '' : 'text-muted' }}">
                    <td>{{ $usuario->name }}</td>
                    <td>{{ $usuario->email }}</td>
                    <td>{{ $usuario->roles->pluck('name')->implode(', ') ?: 'Sin rol' }}</td>
                    <td class="text-center">
                      <span class="badge {{ $activo ? 'badge-success' : 'badge-secondary' }}">{{ $activo ? 'Activo' : 'Inactivo' }}</span>
                    </td>
                    <td class="d-flex">
                      @can('permiso_12')
                      <a href="{{ route('usuarios.edit', $usuario->id) }}" class="btn-xs btn-warning" title="Editar">
                        <i class="fa-solid fa-pen-to-square fa-xs"></i>
                      </a>
                      @endcan
                      @if ($activo)
                      @can('permiso_13')
                      <form method="POST" action="{{ route('usuarios.destroy', $usuario->id) }}"
                        onsubmit="return confirm('¿Desactivar a {{ $usuario->name }}? No podrá ingresar hasta que se lo active.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-xs btn-danger" title="Desactivar">
                          <i class="fa-solid fa-user-slash fa-xs"></i>
                        </button>
                      </form>
                      @endcan
                      @else
                      @can('permiso_12')
                      <form method="POST" action="{{ route('usuarios.activate', $usuario->id) }}"
                        onsubmit="return confirm('¿Activar a {{ $usuario->name }}?');">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn-xs btn-success" title="Activar">
                          <i class="fa-solid fa-user-check fa-xs"></i>
                        </button>
                      </form>
                      @endcan
                      @endif
                    </td>
                  </tr>
                  @empty
                  <tr><td colspan="5" class="text-center text-muted">No hay usuarios para mostrar.</td></tr>
                  @endforelse
                  </tbody>
                </table>
                {{ $usuarios->links() }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endsection