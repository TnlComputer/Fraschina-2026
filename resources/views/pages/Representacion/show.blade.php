@extends('adminlte::page')

@section('title', 'Detalle de Representación')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
  <h1>Detalle de Representación</h1>
  <a href="{{ route('representacion.index') }}" class="btn btn-secondary btn-sm">Volver</a>
</div>
@endsection

@section('content')
<div class="card">
  <div class="card-body">
    {{-- Información General --}}
    <div class="row text-sm">
      <div class="col-md-6">
        <dl class="row">
          <dt class="col-sm-4">Razón Social:</dt>
          <dd class="col-sm-8">{{ $representacion->razonsocial }}</dd>

          <dt class="col-sm-4">Dirección:</dt>
          <dd class="col-sm-8">
            {{ $representacion->dire_calle }} {{ $representacion->dire_nro }},
            Piso: {{ $representacion->piso }},
            Dpto: {{ $representacion->dpto }}
          </dd>

          <dt class="col-sm-4">Código Postal:</dt>
          <dd class="col-sm-8">{{ $representacion->codpost }}</dd>

          <dt class="col-sm-4">Teléfono:</dt>
          <dd class="col-sm-8">{{ $representacion->telefono }}</dd>

          <dt class="col-sm-4">Fax:</dt>
          <dd class="col-sm-8">{{ $representacion->fax }}</dd>

          <dt class="col-sm-4">CUIT:</dt>
          <dd class="col-sm-8">{{ $representacion->cuit }}</dd>
        </dl>
      </div>
      <div class="col-md-6">
        <dl class="row">
          <dt class="col-sm-4">Marcas:</dt>
          <dd class="col-sm-8">{{ $representacion->marcas }}</dd>

          <dt class="col-sm-4">Correo:</dt>
          <dd class="col-sm-8">{{ $representacion->correo }}</dd>

          <dt class="col-sm-4">Zona:</dt>
          <dd class="col-sm-8">{{ optional($representacion->zona)->nombre }}</dd>

          <dt class="col-sm-4">Municipio:</dt>
          <dd class="col-sm-8">{{ optional($representacion->municipio)->ciudadmunicipio }}</dd>

          <dt class="col-sm-4">Localidad:</dt>
          <dd class="col-sm-8">{{ optional($representacion->localidad)->localidad }}</dd>

          <dt class="col-sm-4">Barrio:</dt>
          <dd class="col-sm-8">{{ optional($representacion->barrio)->nombrebarrio }}</dd>
        </dl>
      </div>
    </div>

    {{-- Información Adicional --}}
    <div class="row mt-2">
      <div class="col-md-12">
        <h5>Información:</h5>
        <p>{{ $representacion->info }}</p>
      </div>
    </div>
  </div>
</div>

{{-- Personal Relacionado --}}
@if($representacion->personal->isNotEmpty())
<div class="card mt-4">
  <div class="card-header">
    <h3 class="card-title">Personal Relacionado</h3>
    @can('permiso_11')<a href="{{ route('representacion_personal.create', $representacion->id) }}"
      class="btn btn-primary btn-sm float-right">Nuevo Personal</a>@endcan
  </div>
  <div class="card-body">
    <table class="table table-bordered table-sm w-100">
      <thead>
        <tr class="text-xs">
          <th>Nombre y Apellido</th>
          <th>Tel.Directo</th>
          <th>Int</th>
          <th>Celular</th>
          <th>Tel.Particular</th>
          <th>Email</th>
          <th>Profesión</th>
          <th>Área</th>
          <th>Cargo</th>
          <th>Observaciones</th>
          <th colspan="3"></th>
        </tr>
      </thead>
      <tbody class="text-sm">
        @foreach($representacion->personal as $persona)
        <tr>
          <td>{{ $persona->nombre }} {{ $persona->apellido }}</td>
          <td>{{ $persona->teldirecto }}</td>
          <td>{{ $persona->interno }}</td>
          <td>{{ $persona->telcelular}}</td>
          <td>{{ $persona->telparticular }}</td>
          <td>{{ $persona->email }}</td>
          <td>{{ optional($persona->profesion)->nombreprofesion }}</td>
          <td>{{ optional($persona->area)->area }}</td>
          <td>{{ optional($persona->cargo)->cargo }}</td>
          <td>{{ $persona->observaciones }}</td>
          <td class="d-flex justify-content-between">
            @if($persona->fuera === 0)
            <i class="fas fa-check-circle text-success my-2" title="Activo"></i>
            @else
            <i class="fas fa-times-circle text-danger my-2" title="Desactivado"></i>
            @endif

            @can('permiso_12')
            <a href="{{ route('representacion_personal.edit', $persona->id) }}" class="btn-xs btn-warning m-1"
              title="Editar">
              <i class="fa-solid fa-pen-to-square fa-sm"></i>
            </a>
            @endcan

            @can('permiso_13')
            <form method="POST" action="{{ route('representacion_personal.destroy', $persona->id) }}"
              onsubmit="return confirm('¿Estás seguro de eliminar este registro?');">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn-2xs btn-danger m-1" title="Eliminar">
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
@endif

{{-- Productos Relacionados --}}
@if($representacion->productos->isNotEmpty())
<div class="card mt-4">
  <div class="card-header">
    <h3 class="card-title">Productos Relacionados</h3>
    @can('permiso_11')<a href="{{ route('representacion_producto.create', $representacion->id) }}"
      class="btn btn-primary btn-sm float-right">Nuevo Producto</a>@endcan
  </div>
  <div class="card-body">
    <table class="table table-bordered table-sm w-100">
      <thead>
        <tr class="text-xs">
          <th>Producto</th>
          <th>PL</th>
          <th>P</th>
          <th>L</th>
          <th>W</th>
          <th>Humedoad</th>
          <th>Cenizas</th>
          <th>Gluten Húmedo</th>
          <th>Gluten Seco</th>
          <th>FN</th>
          <th>Estabilidad</th>
          <th>Absorción</th>
          <th>Puntuaciones</th>
          <th>Particularidades</th>
          <td></td>
        </tr>
      </thead>
      <tbody>
        @foreach($representacion->productos as $producto)
        <tr class="text-xs">
          <td>{{ optional($producto->producto)->nombre }}</td>
          <td>{{ $producto->pl }}</td>
          <td>{{ $producto->P }}</td>
          <td>{{ $producto->l }}</td>
          <td>{{ $producto->w }}</td>
          <td>{{ $producto->humedad }}</td>
          <td>{{ $producto->cenizas }}</td>
          <td>{{ $producto->glutenhumedo }}</td>
          <td>{{ $producto->glutenseco }}</td>
          <td>{{ $producto->fn }}</td>
          <td>{{ $producto->estabilidad }}</td>
          <td>{{ $producto->absorcion }}</td>
          <td>{{ $producto->puntuaciones }}</td>
          <td>{{ $producto->particularidades }}</td>
          <td class="d-flex justify-content-between">
            @can('permiso_12')
            <a href="{{ route('representacion_producto.edit', $producto->id) }}" class="btn-xs btn-warning m-1"
              title="Editar">
              <i class="fa-solid fa-pen-to-square fa-sm"></i>
            </a>
            @endcan
            @can('permiso_13')
            <form method="POST" action="{{ route('representacion_producto.destroy', $producto->id) }}"
              onsubmit="return confirm('¿Estás seguro de eliminar este registro?');">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn-2xs btn-danger m-1" title="Eliminar">
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
@endif

@endsection