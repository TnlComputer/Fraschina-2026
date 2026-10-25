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
              <div class="p-2 text-gray-900 text-left text-xs">
                <div class="barra__index d-flex justify-content-between align-items-center mb-3">
                  <div class="div__nuevo">
                    @can('permiso_11')<a href="{{ route('AgendaGral.create') }}" class="btn btn-primary">Nuevo</a>@endcan
                  </div>
                  <div class="div__buscar d-flex">
                    <form method="get" action="{{ route('AgendaGral.index') }}" class="form__buscar d-flex">
                      <input type="text" placeholder="Buscar por nombre, apellido o empresa" name="name" value="{{ $name }}"
                        class="form-control me-2" style="max-width: 350px;">
                      <input type="submit" value="Buscar" class="btn btn-secondary">
                    </form>
                  </div>
                </div>

                {{-- En el celular cada contacto se ve como una tarjeta y los teléfonos se tocan para llamar --}}
                <table class="table table-sm table-striped table-bordered w-100 agenda-tabla">
                  <thead>
                    <tr>
                      <th>Contacto</th>
                      <th>Teléfonos</th>
                      <th>Email</th>
                      <th>Dirección</th>
                      <th>Observaciones</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($agendaGral as $agenda)
                    <tr>
                      <td class="c-contacto">
                        <div class="c-nombre">{{ $agenda->nombre }} {{ $agenda->apellido }}</div>
                        @if (trim((string) $agenda->empresa_institucion) !== '')<div class="c-empresa">{{ $agenda->empresa_institucion }}</div>@endif
                        @if (trim((string) $agenda->profesion_especialidad_oficio) !== '')<div class="c-profesion">{{ $agenda->profesion_especialidad_oficio }}</div>@endif
                      </td>
                      <td class="c-telefonos">
                        @foreach (['Part.' => $agenda->tel_particular, 'Lab.' => $agenda->tel_laboral, 'Cel.' => $agenda->celular] as $tipo => $campo)
                        @foreach (telefonos($campo) as [$texto, $enlace])
                        <div class="agenda-tel">
                          <span class="text-muted">{{ $tipo }}</span>
                          @if ($enlace)<a href="{{ $enlace }}">{{ $texto }}</a>@else{{ $texto }}@endif
                          @if ($tipo === 'Lab.' && $loop->first && trim((string) $agenda->interno) !== '')
                          <span class="text-muted">int. {{ $agenda->interno }}</span>
                          @endif
                        </div>
                        @endforeach
                        @endforeach
                      </td>
                      <td class="c-mail">
                        @if (trim((string) $agenda->mail) !== '')<a href="mailto:{{ trim($agenda->mail) }}">{{ $agenda->mail }}</a>@endif
                      </td>
                      <td class="c-direccion">{{ $agenda->direccion }}</td>
                      <td class="c-obs">{{ $agenda->observaciones }}</td>
                      <td class="c-acciones">
                        <div class="d-flex justify-content-between">
                          @can('permiso_12')
                          <a href="{{ route('AgendaGral.edit', $agenda->id) }}" class="btn-xs btn-warning" title="Editar">
                            <i class="fa-solid fa-pen-to-square fa-xs"></i>
                          </a>
                          @endcan
                          @can('permiso_13')
                          <form method="POST" action="{{ route('AgendaGral.destroy', $agenda->id) }}"
                            onsubmit="return confirm('¿Estás seguro de eliminar este registro?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-xs btn-danger" title="Eliminar">
                              <i class="fa-solid fa-trash fa-xs"></i>
                            </button>
                          </form>
                          @endcan
                        </div>
                      </td>
                    </tr>
                    @empty
                    <tr>
                      <td colspan="8" class="text-center">No hay registros para mostrar...</td>
                    </tr>
                    @endforelse
                  </tbody>
                </table>
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
</div>
@endsection
