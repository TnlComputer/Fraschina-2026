@extends('adminlte::page')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card-header">
        <h3 class="card-title">{{ __('Agro/AgroInd') }}</h3>
      </div>
      <div class="col-12">
        <div class="py-2">
          <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-2 text-gray-900 text-left text-xs">
                <div class="barra__index d-flex justify-content-between align-items-center mb-3">
                  <div class="div__nuevo">
                    @can('permiso_11')<form action="{{ route('agro.create') }}">
                      <input class="btn btn-primary" type="submit" value="Nuevo">
                    </form>@endcan
                  </div>
                  <div class="div__buscar d-flex">
                    <form method="get" action="{{ route('agro.index') }}" class="form__buscar d-flex">
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
                  @forelse($agros as $agro)
                  <tr>
                    <td>{{ $agro->razonsocial }}</td>
                    <td>{{ $agro->dire_calle }}</td>
                    <td>{{ $agro->dire_nro }}</td>
                    <td>{{ $agro->piso }}</td>
                    <td>{{ $agro->dpto }}</td>
                    <td>{{ $agro->codpost }}</td>
                    <td>{{ $agro->barrio }}</td>
                    <td>{{ $agro->municipio }}</td>
                    <td>{{ $agro->localidad }}</td>
                    <td>{{ $agro->telefono }}</td>
                    <td class=" d-flex justify-content-between">
                      <a href="{{ route('agro.show', $agro->id) }}" class="btn-xs btn-info" title="Ver">
                        <i class="fa-regular fa-eye fa-xs align-middle"></i>
                      </a>
                      <a href="{{ route('agro.edit', $agro->id) }}" class="btn-xs btn-warning" title="Editar">
                        <i class="fa-solid fa-pen-to-square fa-xs "></i>
                      </a>
                      <form method="POST" action="{{ route('agro.destroy', $agro->id) }}"
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
                {{ $agros->links() }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endsection