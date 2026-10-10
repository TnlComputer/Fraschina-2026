@extends('adminlte::page')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card-header">
        <h3 class="card-title">{{ __('Tamaños') }}</h3>
      </div>
      <div class="col-12">
        <div class="py-2">
          <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-2 text-gray-900 text-left text-xs">
                <div class="barra__index d-flex justify-content-between align-items-center mb-3">
                  <div class="div__nuevo">
                    @can('permiso_11')<a href="{{ route('tamanios.create') }}" class="btn btn-primary">Nuevo</a>@endcan
                  </div>
                  <div class="div__buscar d-flex">
                    <form method="get" action="{{ route('tamanios.index') }}" class="form__buscar d-flex">
                      @csrf
                      <input type="text" placeholder="Buscar por nombre" name="name" value="{{ $name }}"
                        class="form-control me-2" style="max-width: 350px;">
                      <input type="submit" value="Buscar" class="btn btn-secondary">
                    </form>
                  </div>
                </div>
                <table class="table table-md table-striped table-bordered w-100 text-md">
                  <thead>
                    <tr>
                      <th>Nombre</th>
                      {{-- <th>Email</th>
                      <th>Permiso</th>
                      <th>Estado</th> --}}
                      <th colspan="2"></th>
                    </tr>
                  </thead>
                  @forelse($tamanios as $tamanio)
                  <tr>
                    <td>{{ sin_dato($tamanio->nombre) }}</td>
                    <td class="text-center max-w-10 gap-2">
                      <div class="btn-group" role="group" aria-label="Acciones">
                        <a href="{{ route('tamanios.edit', $tamanio->id) }}" class="btn btn-warning btn-xs"
                          title="Editar">
                          <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <form method="POST" action="{{ route('tamanios.destroy', $tamanio->id) }}"
                          onsubmit="return confirm('¿Estás seguro de eliminar este registro?');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-danger btn-xs" title="Eliminar">
                            <i class="fa-solid fa-trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="3" class="text-center">No hay registros para mostrar...</td>
                  </tr>
                  @endforelse
                </table>
                {{ $tamanios->links() }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endsection