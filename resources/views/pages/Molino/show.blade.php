@extends('adminlte::page')

@section('title', 'Detalle de Molino')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
  <h1>Detalle de Molino</h1>
  <a href="{{ route('molino.index') }}" class="btn btn-secondary btn-sm">Volver</a>
</div>
@endsection
@section('content')
<div class="container-fluid">
  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div class="table-responsive">
            <table class="table table-sm table-bordered">
              <tbody class="text-xs">
                <tr>
                  <th>Razón Social</th>
                  <td>{{ $molino->razonsocial }}</td>
                </tr>
                <tr>
                  <th>Dirección</th>
                  {{-- <td>{{ $molino->dire_calle }} {{ $molino->dire_nro }}{{ $molino->piso }}{{ $molino->dpto }} {{
                    $molino->codpost }}</td> --}}
                  <td>
                    {{ $molino->dire_calle }} {{ $molino->dire_nro }}
                    @if($molino->piso) Piso: {{ $molino->piso }} @endif
                    @if($molino->dpto) Dpto: {{ $molino->dpto }} @endif
                    @if($molino->codpost) ({{ $molino->codpost }}) @endif
                  </td>
                </tr>
                <tr>
                  <th>Barrio</th>
                  <td>{{ $molino->barrio }}</td>
                </tr>
                <tr>
                  <th>Ciudad/Municipio</th>
                  <td>{{ $molino->municipio }}</td>
                </tr>
                <tr>
                  <th>Localidad/Provincia</th>
                  <td>{{ $molino->localidad }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="col-md-6">
          <div class="table-responsive">
            <table class="table table-sm table-bordered">
              <tbody class="text-xs">
                <tr>
                  <th>Email</th>
                  <td>{{ $molino->correo }}</td>
                </tr>
                <tr>
                  <th>Dirección Obs.</th>
                  <td>{{ $molino->dire_obs ?? 'No especificada' }}</td>
                </tr>
                <tr>
                  <th>CUIT</th>
                  <td>{{ $molino->cuit }}</td>
                </tr>

                <tr>
                  <th>Marcas</th>
                  <td>{{ $molino->Marcas ?? 'No especificada' }}</td>
                </tr>
                <tr>
                  <th>Teléfono</th>
                  <td>{{ $molino->telefono }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="col-md-12">
          <div class="table-responsive">
            <table class="table table-sm table-bordered">
              <tbody class="text-xs">
                <tr>
                  <th>Información</th>
                  <td>{{ $molino->info ?? 'No especificada' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Personal Asociado -->
      <h4 class="d-flex justify-content-between align-items-center">
        <span>Personal Asociado</span>
        @can('permiso_11')<a href="{{ route('molino_personal.create', ['molino' => $molino->id]) }}" class="btn btn-primary btn-sm">
          <i class="fas fa-user-plus"></i> Nuevo Personal
        </a>@endcan
      </h4>
      <div class="table-responsive">
        <table class="table table-sm table-bordered">
          <thead class="text-xs">
            <tr>
              <th>Nombre</th>
              <th>Cargo</th>
              <th>Área</th>
              <th>Profesión</th>
              <th>Tel Directo</th>
              <th>Interno</th>
              <th>Tel Celular</th>
              <th>Tel Particular</th>
              <th>Email</th>
              <th>Observaciones</th>
              <th class=" text-center">Acción</th>
            </tr>
          </thead>
          <tbody class="text-xs">
            @foreach($personal as $persona)
            <tr>
              <td>{{ $persona->nombre }} {{ $persona->apellido }}</td>
              <td>{{ $persona->cargo }}</td>
              <td>{{ $persona->area }}</td>
              <td>{{ $persona->profesion }}</td>
              <td>{{ $persona->teldirecto }}</td>
              <td>{{ $persona->interno }}</td>
              <td>{{ $persona->telcelular }}</td>
              <td>{{ $persona->telparticular }}</td>
              <td>{{ $persona->email }}</td>
              <td>{{ $persona->observaciones }}</td>
              <td class="d-flex justify-content-center">
                @if($persona->fuera === 0)
                <i class="fas fa-check-circle text-success my-2" title="Activo"></i>
                @else
                <i class="fas fa-times-circle text-danger my-2" title="Desactivado"></i>
                @endif
                @can('permiso_12')
                <a href="{{ route('molino_personal.edit', $persona->id) }}" class="btn-xs btn-warning mx-1"
                  title="Editar">
                  <i class="fa-solid fa-pen-to-square fa-sm""></i>
                </a>
                @endcan
              @can('permiso_13')
              <form method="POST" action="{{ route('molino_personal.destroy', $persona->id) }}"
                onsubmit="return confirmDelete({{ $persona->id }});">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-xs mx-1" title="Eliminar">
                  <i class="fa-solid fa-trash fa-xs"></i>
                </button>
              </form>
              <script>
                // Función de confirmación personalizada
                function confirmDelete(id) {
                  return confirm(`¿Estás seguro de eliminar este registro?`);
                }
              </script>
              @endcan
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection