@extends('adminlte::page')

@section('title', 'Detalle de Proveedor')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
  <h1>Detalle de Proveedor</h1>
  <a href="{{ route('proveedor.index') }}" class="btn btn-secondary btn-sm">Volver</a>
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
                  <td>{{ $proveedor->razonsocial }}</td>
                </tr>
                <tr>
                  <th>Dirección</th>
                  <td>
                    {{ $proveedor->dire_calle }} {{ $proveedor->dire_nro }}
                    @if($proveedor->piso) Piso: {{ $proveedor->piso }} @endif
                    @if($proveedor->dpto) Dpto: {{ $proveedor->dpto }} @endif
                    @if($proveedor->codpost) ({{ $proveedor->codpost }}) @endif
                  </td>
                </tr>
                <tr>
                  <th>Barrio</th>
                  <td>{{ $proveedor->barrio->nombrebarrio ?? 'No especificado' }}</td>
                </tr>
                <tr>
                  <th>Ciudad/Municipio</th>
                  <td>{{ $proveedor->municipio->ciudadmunicipio ?? 'No especificado' }}</td>
                </tr>
                <tr>
                  <th>Localidad/Provincia</th>
                  <td>{{ $proveedor->localidad->localidad ?? 'No especificada' }}</td>
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
                  <td>{{ $proveedor->correo }}</td>
                </tr>
                <tr>
                  <th>Dirección Obs.</th>
                  <td>{{ $proveedor->dire_obs ?? 'No especificada' }}</td>
                </tr>
                <tr>
                  <th>CUIT</th>
                  <td>{{ $proveedor->cuit }}</td>
                </tr>
                <tr>
                  <th>Marcas</th>
                  <td>{{ $proveedor->marcas ?? 'No especificada' }}</td>
                </tr>
                <tr>
                  <th>Teléfono</th>
                  <td>{{ $proveedor->telefono }}</td>
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
                  <td>{{ $proveedor->info ?? 'No especificada' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Personal Asociado -->
      <h4 class="d-flex justify-content-between align-items-center">
        <span>Personal Asociado</span>
        @can('permiso_11')<a href="{{ route('proveedor_personal.create', ['proveedor' => $proveedor->id]) }}"
          class="btn btn-primary btn-sm">
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
              <th>T.Directo</th>
              <th>Interno</th>
              <th>Celular</th>
              <th>T.Particular</th>
              <th>Email</th>
              <th>Observaciones</th>
              <th class=" text-center w-4">Acción</th>
            </tr>
          </thead>
          <tbody class="text-xs">
            @if(isset($proveedor->personal) && $proveedor->personal->isNotEmpty())
            @foreach($proveedor->personal as $persona)
            <tr>
              <td>{{ $persona->nombre }} {{ $persona->apellido }}</td>
              <td>{{ $persona->cargo->cargo ?? 'No especificado' }}</td>
              <td>{{ $persona->area->area ?? 'No especificada' }}</td>
              <td>{{ $persona->profesion->nombreprofesion ?? 'No especificada' }}</td>
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
                <a href="{{ route('proveedor_personal.edit', $persona->id) }}" class="btn-xs btn-warning mx-1"
                  title="Editar">
                  <i class="fa-solid fa-pen-to-square fa-sm"></i>
                </a>
                @endcan
                @can('permiso_13')
                <form method="POST" action="{{ route('proveedor_personal.destroy', $persona->id) }}"
                  onsubmit="return confirmDelete({{ $persona->id }});">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-xs mx-1" title="Eliminar">
                    <i class="fa-solid fa-trash fa-xs"></i>
                  </button>
                </form>
                <script>
                  function confirmDelete(id) {
                    return confirm(`¿Estás seguro de eliminar este registro?`);
                  }
                </script>
                @endcan
              </td>
            </tr>
            @endforeach
            @else
            <tr>
              <td colspan="10" class="text-center">No hay personal asociado.</td>
            </tr>
            @endif
          </tbody>
        </table>
      </div>

      {{-- Productos Relacionados --}}
      <!-- Personal Asociado -->
      <h4 class="d-flex justify-content-between align-items-center">
        <span>Productos Relacionados</span>
        @can('permiso_11')<a href="{{ route('proveedor_producto.create', ['proveedor' => $proveedor->id]) }}"
          class="btn btn-primary btn-sm">
          <i class="fas fa-user-plus"></i> Nuevo Producto
        </a>@endcan
      </h4>
      <div class="table-responsive">
        <table class="table table-sm table-bordered">
          <thead class="text-xs">
            <tr>
              <th>Producto</th>
              <th>Familia</th>
              <th>Particularidades</th>
              <th class=" text-center w-4">Acción</th>
            </tr>
          </thead>
          <tbody class=" text-xs">
            @if(isset($proveedor->productos) && $proveedor->productos->isNotEmpty())
            @foreach($proveedor->productos as $producto)
            <tr>
              <td>{{ optional($producto->producto)->nombre ?? 'Sin nombre' }}</td>
              <td>{{ $producto->familia ?? 'Sin familia' }}</td>
              <td>{{ $producto->particularidades ?? 'N/A' }}</td>
              <td class="text-center" style=" width: 1%; white-space: nowrap; padding: 5px;">
                @can('permiso_12')
                <a href="{{ route('proveedor_producto.edit', $producto->id) }}" class="btn btn-warning btn-xs"
                  title="Editar">
                  <i class="fa-solid fa-pen-to-square"></i>
                </a>
                @endcan
                @can('permiso_13')
                <form method="POST" action="{{ route('proveedor_producto.destroy', $producto->id) }}"
                  onsubmit="return confirm('¿Estás seguro de eliminar este registro?');" style="display:inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-xs" title="Eliminar">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </form>
                @endcan
              </td>
            </tr>
            @endforeach
            @else
            <tr>
              <td colspan="10" class="text-center">No hay producto asociado.</td>
            </tr>
            @endif
          </tbody>
        </table>
      </div>
      {{-- @endif --}}
    </div>
  </div>
</div>
@endsection