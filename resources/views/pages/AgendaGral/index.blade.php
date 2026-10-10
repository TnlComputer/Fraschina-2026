@extends('adminlte::page')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card-header">
        <h3 class="card-title">{{ __('Agenda General') }}</h3>
      </div>
      <div class="col-12">
        <div class="py-2">
          <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-4 text-gray-900">
                <div class="d-flex justify-content-between mb-3">
                  <form action="#" method="get">
                    <input type="text" placeholder="Type to search" name="name" value="{{ $name }}" class="form-control"
                      style="width: 300px; display: inline;">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                  </form>
                  @can('permiso_11')<a href="{{ route('AgendaGral.create') }}" class="btn btn-success">Nuevo</a>@endcan
                </div>

                <div class="table-responsive">
                  <!-- Contenedor responsivo para la tabla -->
                  <table class="table table-striped table-bordered table-hover table-sm w-100">
                    <!-- Aseguramos que la tabla ocupe todo el ancho -->
                    <thead>
                      <tr class="text-xs">
                        <th>Nombre y Apellido</th>
                        <th>Empresa-Institución</th>
                        <th>Profesión-Especialidad-Oficio</th>
                        <th>Teléfono Particular</th>
                        <th>Teléfono Laboral</th>
                        <th>Int</th>
                        <th>Celular</th>
                        <th>Email</th>
                        <th>Dirección</th>
                        <th>Observaciones</th>
                        <th>Acciones</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($agendaGral as $agenda)
                      <tr class="text-xs">
                        <td>{{ $agenda->nombre }} {{ $agenda->apellido }}</td>
                        <td>{{ $agenda->empresa_institucion }}</td>
                        <td>{{ $agenda->profesion_especialidad_oficio }}</td>
                        <td>{{ $agenda->tel_particular }}</td>
                        <td>{{ $agenda->tel_laboral }}</td>
                        <td>{{ $agenda->interno }}</td>
                        <td>{{ $agenda->celular }} </td>
                        <td>{{ $agenda->mail }}</td>
                        <td>{{ $agenda->direccion }}</td>
                        <td>{{ $agenda->observaciones }}</td>
                        <td class=" d-flex justify-content-between">
                          <div class="btn-group" role="group">
                            <a href="{{ route('AgendaGral.edit', $agenda->id) }}" class="btn-xs btn-warning m-1">
                              <i class="fa-solid fa-pen-to-square fa-sm"></i>
                            </a>
                            <form method="POST" action="{{ route('AgendaGral.destroy', $agenda->id) }}"
                              style="display: inline;">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn-2xs btn-danger m-1">
                                <i class="fa-solid fa-trash fa-xs"></i>
                              </button>
                            </form>
                          </div>
                        </td>
                      </tr>
                      @empty
                      <tr>
                        <td colspan="12" class="text-center">No hay registros para mostrar...</td>
                      </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
                <div class="d-flex justify-content-center">
                  {{ $agendaGral->links('pagination::bootstrap-4') }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endsection