@extends('adminlte::page')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card-header">
        <h3 class="card-title">{{ __('Proveedor') }}</h3>
      </div>
      <div class="col-12">
        <div class="py-2">
          <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-2 text-gray-900 text-left text-xs">
                <div class="barra__index d-flex justify-content-between align-items-center mb-3">
                  <div class="div__nuevo">
                    @can('permiso_11')<form action="{{ route('proveedor.create') }}">
                      <input class="btn btn-primary" type="submit" value="Nuevo">
                    </form>@endcan
                  </div>
                  <div class="div__buscar d-flex">
                    <form method="get" action="{{ route('proveedor.index') }}" class="form__buscar d-flex">
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
                  @forelse($proveedores as $proveedor)
                  <tr>
                    <td>{{ $proveedor->razonsocial }}</td>
                    <td>{{ $proveedor->dire_calle }}</td>
                    <td>{{ $proveedor->dire_nro }}</td>
                    <td>{{ $proveedor->piso }}</td>
                    <td>{{ $proveedor->dpto }}</td>
                    <td>{{ $proveedor->codpost }}</td>
                    <td>{{ $proveedor->barrio }}</td>
                    <td>{{ $proveedor->municipio }}</td>
                    <td>{{ $proveedor->localidad }}</td>
                    <td>{{ $proveedor->telefono }}</td>
                    <td class=" d-flex justify-content-between">
                      <a href="{{ route('proveedor.show', $proveedor->id) }}" class="btn-xs btn-info" title="Ver">
                        <i class="fa-regular fa-eye fa-xs align-middle"></i>
                      </a>
                      <a href="{{ route('proveedor.edit', $proveedor->id) }}" class="btn-xs btn-warning" title="Editar">
                        <i class="fa-solid fa-pen-to-square fa-xs "></i>
                      </a>
                      <form method="POST" action="{{ route('proveedor.destroy', $proveedor->id) }}"
                        onsubmit="return confirm('¿Estás seguro de eliminar este registro?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-xs btn-danger" title="Eliminar">
                          <i class="fa-solid fa-trash fa-xs"></i>
                        </button>
                      </form>
                    </td>

                  </tr>
                  @empty
                  <p>No hay registros para mostrar...</p>
                  @endforelse
                </table>
                {{ $proveedores->links() }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endsection