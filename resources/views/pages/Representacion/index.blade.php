@extends('adminlte::page')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card-header">
        <h3 class="card-title">{{ __('Representación') }}</h3>
      </div>
      <div class="col-12">
        <div class="py-2">
          <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-2 text-gray-900 text-left text-xs">
                <div class="barra__index d-flex justify-content-between align-items-center mb-3">
                  <div class="div__nuevo">
                    @can('permiso_11')<form action="{{ route('representacion.create') }}">
                      <input class="btn btn-primary" type="submit" value="Nuevo">
                    </form>@endcan
                  </div>
                  <div class="div__buscar d-flex">
                    <form method="get" action="{{ route('representacion.index') }}" class="form__buscar d-flex">
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
                      <th class="">Razón social</th>
                      <th class="">Dirección</th>
                      <th class="">Barrio</th>
                      <th class="">Localidad</th>
                      <th class="">Zona</th>
                      <th class="">Teléfono</th>
                      <th class="">Email</th>
                      <th class="">Cuit</th>
                      <th class="">Marcas</th>
                      <th></th>
                    </tr>
                  </thead>
                  @forelse($representaciones as $representacion)
                  <tr>
                    <td data-titulo="Razón social">{{ $representacion->razonsocial }}</td>
                    <td data-titulo="Dirección">
                      {{ $representacion->dire_calle }}
                      {{ $representacion->dire_nro }}
                      @if($representacion->piso != '')
                      Piso {{ $representacion->piso }}
                      @endif
                      @if($representacion->dpto != '')
                      Dpto {{ $representacion->dpto }}
                      @endif
                      @if($representacion->codpost != '')
                      ({{ $representacion->codpost }})
                      @endif
                    </td>
                    <td data-titulo="Barrio">{{ $representacion->barrio }}</td>
                    <td data-titulo="Localidad">{{ $representacion->localidad }}</td>
                    <td data-titulo="Zona">{{ $representacion->zona }}</td>
                    <td data-titulo="Teléfono">{{ $representacion->telefono }}</td>
                    <td class="" data-titulo="Email">{{ $representacion->correo }}</td>
                    <td data-titulo="Cuit">{{ $representacion->cuit }}</td>
                    <td data-titulo="Marcas">{{ $representacion->marcas }}</td>
                    <td class="d-flex justify-content-between">
                      <a href="{{ route('representacion.show', $representacion->id) }}" class="btn-xs btn-info m-1"
                        title="Ver">
                        <i class="fa-regular fa-eye fa-sm align-middle"></i>
                      </a>
                      @can('permiso_12')
                      <a href="{{ route('representacion.edit', $representacion->id) }}" class="btn-xs btn-warning m-1"
                        title="Editar">
                        <i class="fa-solid fa-pen-to-square fa-sm"></i>
                      </a>
                      @endcan
                      @can('permiso_13')
                      <form method="POST" action="{{ route('representacion.destroy', $representacion->id) }}"
                        onsubmit="return confirm('¿Estás seguro de eliminar este registro?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                          class="btn-2xs btn-{{ $representacion->status ? 'danger' : 'success' }} m-1"
                          title="{{ $representacion->status ? 'Desactivar' : 'Activar' }}">
                          <i class="fa-solid fa-{{ $representacion->status ? 'trash' : 'check' }} fa-xs"></i>
                        </button>
                      </form>
                      @endcan
                    </td>
                    @empty
                  <tr>
                    <td colspan="10" class="text-center text-red-600 text-2xl">
                      No hay registros para mostrar...
                    </td>
                  </tr>
                  @endforelse
                </table>
                {{ $representaciones->links() }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection