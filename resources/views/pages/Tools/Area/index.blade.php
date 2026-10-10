@extends('adminlte::page')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card-header">
        <h3 class="card-title">{{ __('Areas') }}</h3>
      </div>
      <div class="col-12">
        <div class="py-2">
          <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-2 text-gray-900 text-left text-xs">
                <div class="barra__index d-flex justify-content-between align-items-center mb-3">
                  <div class="div__nuevo">
                    @can('permiso_11')<form action="{{ route('areas.create') }}">
                      <input class="btn btn-primary" type="submit" value="Nuevo">
                    </form>@endcan
                  </div>
                  <div class="div__buscar d-flex">
                    <form method="get" action="{{ route('areas.index') }}" class="form__buscar d-flex">
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
                      <th>Area</th>
                      {{-- <th class="text-center">Color Acción</th>
                      <th class="text-center">Color Código</th>
                      <th class="text-center">Status</th> --}}
                      <th class="text-center"></th>
                    </tr>
                  </thead>
                  @forelse($areas as $area)
                  <tr>
                    <td>{{ sin_dato($area->area) }}</td>
                    {{-- <td class="text-center">{{ $area->colorAcc }}</td>
                    <td class="text-center">{{ $area->colorCod }}</td>
                    <td class="text-center">{{ $area->status }}</td> --}}
                    <td class="d-flex gap-1 justify-content-center">
                      <a href="{{ route('areas.edit', $area->id) }}" class="btn btn-warning btn-xs" title="Editar">
                        <i class="fa-solid fa-pen-to-square fa-xs"></i>
                      </a>
                      <form method="POST" action="{{ route('areas.destroy', $area->id) }}"
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
                {{ $areas->links() }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endsection