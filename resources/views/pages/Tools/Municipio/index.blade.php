@extends('adminlte::page')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card-header">
        <h3 class="card-title">{{ __('municipios') }}</h3>
      </div>
      <div class="col-12">
        <div class="py-2">
          <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-2 text-gray-900 text-left text-xs">
                <div class="barra__index d-flex justify-content-between align-items-center mb-3">
                  <div class="div__nuevo">
                    <form action="{{ route('ciudades_municipios.create') }}">
                      <input class="btn btn-primary" type="submit" value="Nuevo">
                    </form>
                  </div>
                  <div class="div__buscar d-flex">
                    <form method="get" action="{{ route('ciudades_municipios.index') }}" class="form__buscar d-flex">
                      @csrf
                      <input type="text" placeholder="Buscar por nombre" name="name" value="{{ $name }}"
                        class="form-control me-2" style="max-width: 350px;">
                      <input type="submit" value="Buscar" class="btn btn-secondary">
                    </form>
                  </div>
                </div>
                <table class="table table-sm table-striped table-bordered text-md">
                  <thead>
                    <tr>
                      <th>Nombre municipio</th>
                      <th class="text-center">Representacion</th>
                      <th class="text-center">Distribucion</th>
                      <th class="text-center">Molinos</th>
                      <th class="text-center">Preveedores</th>
                      <th class="text-center">Agros</th>
                      <th class="text-center">Transportes</th>
                      <th class="text-center">Agendas</th>
                      <th class="text-center"></th>
                    </tr>
                  </thead>
                  @forelse($municipios as $municipio)
                  <tr>
                    <td>{{ sin_dato($municipio->ciudadmunicipio) }}</td>
                    <td class="text-center">{{ $municipio->representacion }}</td>
                    <td class="text-center">{{ $municipio->distribucion }}</td>
                    <td class="text-center">{{ $municipio->molinos }}</td>
                    <td class="text-center">{{ $municipio->preveedores }}</td>
                    <td class="text-center">{{ $municipio->agros }}</td>
                    <td class="text-center">{{ $municipio->transportes }}</td>
                    <td class="text-center">{{ $municipio->agendas }}</td>
                    <td class="d-flex gap-1 justify-content-center">
                      <a href="{{ route('ciudades_municipios.edit', $municipio->id) }}" class="btn btn-warning btn-xs"
                        title="Editar">
                        <i class="fa-solid fa-pen-to-square fa-xs"></i>
                      </a>
                      <form method="POST" action="{{ route('ciudades_municipios.destroy', $municipio->id) }}"
                        onsubmit="return confirm('¿Estás seguro de eliminar este registro?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-xs" title="Eliminar">
                          <i class="fa-solid fa-trash fa-xs"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                  @empty
                  <p>No hay registros para mostrar...</p>
                  @endforelse
                </table>
                {{ $municipios->links() }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endsection