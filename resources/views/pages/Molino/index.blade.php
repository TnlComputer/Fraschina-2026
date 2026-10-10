@extends('adminlte::page')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card-header">
        <h3 class="card-title">{{ __('Molinos') }}</h3>
      </div>
      <div class="col-12">
        <div class="py-2">
          <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-2 text-gray-900 text-left text-xs">
                <div class="barra__index d-flex justify-content-between align-items-center mb-3">
                  <div class="div__nuevo">
                    @can('permiso_11')<form action="{{ route('molino.create') }}">
                      <input class="btn btn-primary" type="submit" value="Nuevo">
                    </form>@endcan
                  </div>
                  <div class="div__buscar d-flex">
                    <form method="get" action="{{ route('molino.index') }}" class="form__buscar d-flex">
                      @csrf
                      <input type="text" placeholder="Buscar por nombre" name="name" value="{{ $name }}"
                        class="form-control me-2" style="max-width: 350px;">
                      <input type="submit" value="Buscar" class="btn btn-secondary">
                    </form>
                  </div>
                </div>
                <table class="table table-sm table-striped table-bordered w-100">
                  <thead>
                    <tr>
                      <th>Razón social</th>
                      <th colspan="4">Dirección</th>
                      <th>Cod.Post</th>
                      <th>Barrio</th>
                      <th>Municipio</th>
                      <th>Localidad</th>
                      <th>Telefono</th>
                      <th></th>
                    </tr>
                  </thead>
                  @forelse($molinos as $molino)
                  <tr>
                    <td>{{ $molino->razonsocial }}</td>
                    <td>{{ $molino->dire_calle }}</td>
                    <td>{{ $molino->dire_nro }}</td>
                    <td>{{ $molino->piso }}</td>
                    <td>{{ $molino->dpto }}</td>
                    <td>{{ $molino->codpost }}</td>
                    <td>{{ $molino->barrio }}</td>
                    <td>{{ $molino->municipio }}</td>
                    <td>{{ $molino->localidad }}</td>
                    <td>{{ $molino->telefono }}</td>
                    <td class=" d-flex justify-content-between">
                      <a href="{{ route('molino.show', $molino->id) }}" class="btn-xs btn-info" title="Ver">
                        <i class="fa-regular fa-eye fa-xs align-middle"></i>
                      </a>
                      @can('permiso_12')
                      <a href="{{ route('molino.edit', $molino->id) }}" class="btn-xs btn-warning" title="Editar">
                        <i class="fa-solid fa-pen-to-square fa-xs "></i>
                      </a>
                      @endcan
                      @can('permiso_13')
                      <form method="POST" action="{{ route('molino.destroy', $molino->id) }}"
                        onsubmit="return confirm('¿Estás seguro de eliminar este registro?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-xs btn-danger" title="Eliminar">
                          <i class="fa-solid fa-trash fa-xs"></i>
                        </button>
                      </form>
                      @endcan
                    </td>
                  </tr>
                  @empty
                  <p>No hay registros para mostrar...</p>
                  @endforelse
                </table>
                {{ $molinos->links() }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endsection