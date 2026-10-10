@extends('adminlte::page')

@section('title', 'Detalle de Distribución')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
  <h1>Detalle de Distribución</h1>
  <a href="{{ route('distribucion.index') }}" class="btn btn-secondary btn-sm">Volver</a>
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
                  <th>Nro. GS</th>
                  <td>
                    @if ($distribucion->clisg_id > 0)
                    {{ $distribucion->clisg_id }}
                    @else
                    No disponible
                    @endif
                  </td>
                </tr>
                <tr>
                  <th>Nombre Fantasía</th>
                  <td>{{ $distribucion->nomfantasia }}</td>
                </tr>
                <tr>
                  <th>Razón Social</th>
                  <td>{{ $distribucion->razonsocial }}</td>
                </tr>
                <tr>
                  <th>Teléfono</th>
                  <td>{{ $distribucion->telefono }}</td>
                </tr>
                <tr>
                  <th>Email</th>
                  <td>{{ $distribucion->correo }}</td>
                </tr>
                <tr>
                  <th>CUIT/DNI</th>
                  <td>{{ $distribucion->cuit }}</td>
                </tr>
                <tr>
                  <th>Estado</th>
                  <td>{{ $distribucion->estado }}</td>
                </tr>
                <tr>
                  <th>Veraz</th>
                  <td>{{ $distribucion->veraz ?? 'No disponible' }}</td>
                </tr>
                <tr>
                  <th>Recibe x la mañana</th>
                  <td>{{ horario($distribucion->desde, $distribucion->hasta) }}</td>
                </tr>
                <tr>
                  <th>Recibe x la tarde</th>
                  <td>{{ horario($distribucion->desde1, $distribucion->hasta1) }}</td>
                </tr>
                <tr>
                  <th>Lunes Cerrado</th>
                  <td>{{ \App\Models\Distribucion::esSi($distribucion->lunes) ? 'Sí' : 'No' }}</td>
                </tr>
                <tr>
                  <th>Sábado Recibe</th>
                  <td>{{ \App\Models\Distribucion::esSi($distribucion->sabado) ? 'Sí' : 'No' }}</td>
                </tr>
                <tr>
                  <th>Marcas</th>
                  <td>{{ $distribucion->Marcas ?? 'No especificada' }}</td>
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
                  <th>Dirección</th>
                  <td>{{ $distribucion->dire_calle }} {{ $distribucion->dire_nro }}</td>
                </tr>
                <tr>
                  <th>Barrio</th>
                  <td>{{ $distribucion->barrio }}</td>
                </tr>
                <tr>
                  <th>Ciudad/Municipio</th>
                  <td>{{ $distribucion->municipio }}</td>
                </tr>
                <tr>
                  <th>Localidad/Provincia</th>
                  <td>{{ $distribucion->localidad }}</td>
                </tr>
                <tr>
                  <th>Zona</th>
                  <td>{{ $distribucion->zona }}</td>
                </tr>
                <tr>
                  <th>Rubro/Tamaño/Modo</th>
                  <td>{{ $distribucion->rubro }}
                    @if ($distribucion->tamanio) - {{ $distribucion->tamanio }} @endif
                    @if ($distribucion->modo) - {{ $distribucion->modo }} @endif
                  </td>
                </tr>
                <tr>
                  <th>Productos</th>
                  <td>{{ $distribucion->productoCDA ?? 'No disponibles' }}</td>
                </tr>
                <tr>
                  <th>Observaciones Recepción</th>
                  <td>{{ $distribucion->obsrecep ?? 'No disponibles' }}</td>
                </tr>
                <tr>
                  <th>Cobrar</th>
                  <td>{{ $distribucion->cobrar ?? 'No especificada' }}</td>
                </tr>
                <tr>
                  <th>Condición de Pago</th>
                  <td>{{ $distribucion->cobro ?? 'No especificada' }}</td>
                </tr>
                <tr>
                  <th>Forma de Pago</th>
                  <td>{{ $distribucion->tpago ?? 'No especificada' }}</td>
                </tr>
                <tr>
                  <th>Factura Impresa</th>
                  <td>{{ \App\Models\Distribucion::esSi($distribucion->fac_imp) ? 'Sí' : 'No' }}</td>
                </tr>
                <tr>
                  <th>Contacto inicial</th>
                  <td>{{ $distribucion->contacto ?? 'Sin contacto' }}</td>
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
                  <td>{{ $distribucion->info ?? 'No especificada' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Personal Asociado -->
      <h4 class="d-flex justify-content-between align-items-center">
        <span>Personal Asociado</span>
        @can('permiso_11')<a href="{{ route('distribucion_personal.create', ['distribucion' => $distribucion->id]) }}"
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
                <a href="{{ route('distribucion_personal.edit', $persona->id) }}" class="btn-xs btn-warning mx-1"
                  title="Editar">
                  <i class="fa-solid fa-pen-to-square fa-sm""></i>
                </a>
                @endcan
              @can('permiso_13')
              <form method=" POST" action="{{ route('distribucion_personal.destroy', $persona->id) }}"
                    onsubmit="return confirm('¿Estás seguro de eliminar este registro?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-2xs btn-danger mx-1" title="Eliminar">
                      <i class="fa-solid fa-trash fa-xs"></i>
                    </button>
                    </form>
                    @endcan
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <!-- Productos Asociados -->
      <h4 class="d-flex justify-content-between align-items-center">
        <span>Productos Asociados</span>
        @can('permiso_11')<a href="{{ route('distribucion_producto.create', ['distribucion' => $distribucion->id]) }}"
          class="btn btn-primary btn-sm">
          <i class="fas fa-box"></i> Nuevo Producto
        </a>@endcan
      </h4>
      <div class="table-responsive">
        <table class="table table-sm table-bordered">
          <thead class="text-xs">
            <tr>
              <th>Producto</th>
              <th class=" text-center">Precio</th>
              <th class=" text-center">Fecha Última Cotización</th>
              <th class=" text-center">Fecha Última Entrega</th>
              <th class=" text-center">Acción</th>
            </tr>
          </thead>
          <tbody class="text-xs">
            @foreach($productos as $producto)
            <tr>
              <td>{{ $producto->nomproducto }}</td>
              <td class=" text-right">{{ numero($producto->precio, 2) }}</td>
              <td class=" text-center">{{ fecha($producto->fecha) }}</td>
              <td class=" text-center">{{ fecha($producto->fechaUEnt) }}</td>
              <td class="d-flex justify-content-center">
                @can('permiso_12')
                <a href="{{ route('distribucion_producto.edit', $producto->id) }}" class="btn-xs btn-warning mx-1"
                  title="Editar">
                  <i class="fa-solid fa-pen-to-square fa-sm"></i>
                </a>
                @endcan
                @can('permiso_13')
                <form method="POST" action="{{ route('distribucion_producto.destroy', $producto->id) }}"
                  onsubmit="return confirm('¿Estás seguro de eliminar este registro?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn-2xs btn-danger mx-1" title="Eliminar">
                    <i class="fa-solid fa-trash fa-xs"></i>
                  </button>
                </form>
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