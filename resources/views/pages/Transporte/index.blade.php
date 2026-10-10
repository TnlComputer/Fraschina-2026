@extends('adminlte::page')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card-header">
        <h3 class="card-title">{{ __('Transporte') }}</h3>
      </div>
      <div class="col-12">
        <div class="py-2">
          <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-2 text-gray-900 text-left text-xs">
                <div class="barra__index d-flex justify-content-between align-items-center mb-3">
                  <div class="div__nuevo">
                    @can('permiso_11')<form action="{{ route('transporte.create') }}">
                      <input class="btn btn-primary" type="submit" value="Nuevo">
                    </form>@endcan
                  </div>
                  <div class="div__buscar d-flex">
                    <form method="get" action="{{ route('transporte.index') }}" class="form__buscar d-flex">
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
                      <th>Telefono</th>
                      <th></th>
                    </tr>
                  </thead>
                  @forelse($transportes as $transporte)
                  <tr>
                    <td>{{ $transporte->razonsocial }}</td>
                    <td>{{ $transporte->dire_calle }}</td>
                    <td>{{ $transporte->dire_nro }}</td>
                    <td>{{ $transporte->piso }}</td>
                    <td>{{ $transporte->dpto }}</td>
                    <td>{{ $transporte->codpost }}</td>
                    <td>{{ $transporte->telefono }}</td>
                    <td class=" d-flex justify-content-between">
                      <a href="{{ route('transporte.show', $transporte->id) }}" class="btn-xs btn-info" title="Ver">
                        <i class="fa-regular fa-eye fa-xs align-middle"></i>
                      </a>
                      @can('permiso_12')
                      <a href="{{ route('transporte.edit', $transporte->id) }}" class="btn-xs btn-warning"
                        title="Editar">
                        <i class="fa-solid fa-pen-to-square fa-xs "></i>
                      </a>
                      @endcan
                      @can('permiso_13')
                      <form method="POST" action="{{ route('transporte.destroy', $transporte->id) }}"
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
                {{ $transportes->links() }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endsection