@extends('adminlte::page')

@section('title', 'Agenda de Distribución')

@section('content_header')
<h1>Agenda de Distribución</h1>
@php
  $hoy = now()->toDateString();
  $manana = now()->addDay()->toDateString();
  $filtro = request('dia');
@endphp
<div class="barra__index">
  @can('permiso_11')<a href="{{ route('distribucion_agenda.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Nuevo contacto</a>@endcan
  @can('permiso_11')<a href="{{ route('distribucion_pedido.create') }}" class="btn btn-info"><i class="fas fa-plus"></i> Nuevo pedido</a>@endcan

  <div class="btn-group ml-2" role="group" aria-label="Filtrar por día">
    <a href="{{ route('distribucion_agenda.index', array_filter(['search' => request('search')])) }}" class="btn btn-outline-secondary {{ !$filtro ? 'active' : '' }}">Todos</a>
    <a href="{{ route('distribucion_agenda.index', array_filter(['dia' => 'hoy', 'search' => request('search')])) }}" class="btn btn-outline-secondary {{ $filtro === 'hoy' ? 'active' : '' }}">Hoy</a>
    <a href="{{ route('distribucion_agenda.index', array_filter(['dia' => 'manana', 'search' => request('search')])) }}" class="btn btn-outline-secondary {{ $filtro === 'manana' ? 'active' : '' }}">Mañana</a>
  </div>

  <div class="div__buscar">
    <form method="GET" action="{{ route('distribucion_agenda.index') }}" class="form__buscar d-flex">
      <input type="date" name="dia" class="form-control mr-1" style="max-width: 170px" data-dias="agenda" data-dias-enviar
        value="{{ in_array($filtro, ['hoy', 'manana']) ? '' : $filtro }}" title="Ver un día">
      <input type="text" name="search" class="form-control" placeholder="Buscar cliente, tema, contacto..." value="{{ request('search') }}">
      <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
    </form>
  </div>
</div>
@if ($dia)
<div class="small text-muted mb-2">Mostrando el {{ fecha($dia) }} · {{ $agendas->total() }} {{ $agendas->total() === 1 ? 'contacto' : 'contactos' }}</div>
@endif
@stop

@section('content')
<div class="container-fluid">
  <!-- Tabla con paginación -->
  <div class="table-responsive">
    <table class="table table-bordered text-sm w-100">
      <thead>
        <tr>
          <th class=" text-center">Fecha</th>
          <th class=" text-center">Prioridad</th>
          <th class=" text-center">Hora</th>
          <th class=" text-center">Auto</th>
          <th>Nombre Fantasía</th>
          <th class=" text-center">Estado</th>
          <th class=" text-center">Acción</th>
          <th>Temas</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($agendas as $agenda)
        @php $colorPrioridad = color_css($agenda->auxprioridades?->color); @endphp
        <tr style="border-left: 5px solid {{ $colorPrioridad ? str_replace(['#f8d7da', '#d4edda', '#fff3cd'], ['#dc3545', '#28a745', '#ffc107'], $colorPrioridad) : 'transparent' }}">
          <td class=" text-center text-nowrap">{{ fecha($agenda->fecha) }}</td>
          <td class="text-center">@if (trim((string) $agenda->auxprioridades?->nombre) !== '')<span class="badge" style="background: {{ $colorPrioridad ?? '#e2e3e5' }}">{{ $agenda->auxprioridades->nombre }}</span>@endif</td>
          <td class="text-center">
            {{ $agenda->hs ? \Carbon\Carbon::createFromFormat('H:i:s', $agenda->hs)->format('H:i') : '' }}
          </td>
          <td class=" text-center">{{ $agenda->distribucion->auto }}</td>
          <td>
            <span class=" text-capitalize">
              {{ $agenda->distribucion->nomfantasia ?? '' }}
            </span>
          </td>
          <td class=" text-center">{{ $agenda->distribucion->auxestado->nomEstado ?? '' }}</td>
          <td class=" text-center text-capitalize"
            style="background-color: {{ color_css($agenda->auxacciones?->colorAcc, 'transparent') }};">{{
            $agenda->auxacciones->accion ?? '' }}</td>
          <td>{{ Str::limit($agenda->temas, 80) }}</td>
          {{-- <td>
            <button class="btn btn-info btn-xs" data-toggle="modal" data-target="#modal{{ $agenda->id }}">
              <i class="fas fa-eye"></i>
            </button>
          </td> --}}
          <td class="d-flex justify-content-center">
            <!-- Botón de Ver Más -->
            <button class="btn btn-info btn-xs d-inline mr-2" data-toggle="modal" data-target="#modal{{ $agenda->id }}">
              <i class="fas fa-eye fs-sm"></i>
            </button>
            @can('permiso_12')
            <!-- Botón de Editar -->
            <a href="{{ route('distribucion_agenda.edit', $agenda->id) }}" class="btn btn-warning btn-xs d-inline mr-2">
              <i class="fas fa-edit fa-sm"></i>
            </a>
            @endcan
            @can('permiso_13')
            <!-- Formulario de eliminación (cambio de estado a D) -->
            <form action="{{ route('distribucion_agenda.destroy', $agenda->id) }}" method="POST" class="d-inline">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger btn-xs"
                onclick="return confirm('¿Estás seguro de que quieres desactivar este registro?')">
                <i class="fas fa-trash-alt fa-sm"></i>
              </button>
            </form>
            @endcan
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <!-- Paginación -->
  <div class="pagination justify-content-center">
    {{ $agendas->links() }}
  </div>

  <!-- Modales -->
  @foreach($agendas as $agenda)
  <div class="modal fade" id="modal{{ $agenda->id }}" tabindex="-1" role="dialog"
    aria-labelledby="modalLabel{{ $agenda->id }}" aria-hidden="true">
    <div class="modal-dialog modal-md modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalLabel{{ $agenda->id }}"><strong>Nombre Fantasia:</strong> {{
            $agenda->distribucion->nomfantasia }}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <strong>Razón Social:</strong> {{ $agenda->distribucion->razonsocial }} <br>
          <strong>Fecha:</strong> {{ fecha($agenda->fecha) }} <br>
          <strong>Hora:</strong> {{ hora($agenda->hs) ?: '—' }} <br>
          <strong>Auto:</strong> {{ $agenda->distribucion->auto }} <br>
          <strong>Veráz:</strong> {{ $agenda->distribucion->auxveraz->estado ?? 'No disponible' }} <br>
          <strong>Estado:</strong> {{ $agenda->distribucion->auxestado->nomEstado ?? 'No disponible' }} <br>
          <strong>Acción:</strong> <span style="background-color: {{ color_css($agenda->auxacciones?->colorAcc, 'transparent') }};">
            {{ $agenda->auxacciones?->accion ?? '' }} </span><br>
          <strong>Prioridad:</strong><span
            style="background-color: {{ color_css($agenda->auxprioridades?->color, 'transparent') }};">
            {{ $agenda->auxprioridades?->nombre ?? 'No disponible' }} </span> <br>
          <strong>Cargo:</strong>{{ $agenda->distribucionPersonal?->cargo?->cargo ?? '' }} <br>
          <strong>Persona:</strong> {{
          $agenda->distribucionPersonal->nombre ?? '' }} {{ $agenda->distribucionPersonal->apellido ?? '' }} <br>
          <strong>Teléfono:</strong> {{ $agenda->distribucion->telefono }} <br>
          <strong>Temas:</strong>
          <span id="temas{{ $agenda->id }}">{{ $agenda->temas }}</span>
          <div id="full-temas{{ $agenda->id }}" style="display: none;">{{ $agenda->temas }}</div><br>
          <strong>Ult. cotiz./venta:</strong> {{ $agenda->cotizacion }} <br>
          <strong>Fecha ult. cotiz./venta:</strong> {{ fecha($agenda->fecCot) }} <br>
          {{-- última entrega: la del pedido más reciente, o la que guardó el sistema anterior (fecCotEnt) --}}
          @php $ultimaEntrega = $agenda->distribucion->distribucionLineaPedidos->max('fechaEntrega') ?: $agenda->fecCotEnt; @endphp
          <strong>Fecha Ult.Entrega:</strong> {{ fecha($ultimaEntrega) }} <br>
          <strong>Información:</strong> {{ $agenda->distribucion->info }} <br>

          <hr class=" bg-green-600">
          <strong>Calle</strong> {{ $agenda->distribucion->auxCalles?->calle ?? 'Sin información' }} <br>
          <strong>Altura</strong> {{ $agenda->distribucion->dire_nro }} {{ $agenda->distribucion->piso }} {{
          $agenda->distribucion->dpto }}<br>
          <strong>Barrio</strong> {{ $agenda->distribucion->auxbarrio?->nombrebarrio }} <br>
          <strong>Ciudad/Municipio</strong> {{ $agenda->distribucion->auxmunicipio?->ciudadmunicipio }} <br>
          <strong>Localidad</strong> {{ $agenda->distribucion->auxlocalidad?->localidad }} <br>
          <strong>Zona</strong> {{ $agenda->distribucion->auxzona?->nombre }} <br>

          <hr class=" bg-blue-600">
          <strong>Rubro</strong> {{ $agenda->distribucion->auxrubro?->nombre }} <br>
          <strong>Tamaño</strong> {{ $agenda->distribucion->auxtamanio?->nombre }} <br>
          <strong>Modo</strong> {{ $agenda->distribucion->auxmodo?->nombre }} <br>
          <strong>Productos</strong> {{ $agenda->distribucion->productoCDA }} <br>
          <strong>Contacto Inicial</strong> {{ $agenda->distribucion->auxcontacto?->contacto }} <br>

          <hr class="bg-blue-900">
          <strong>Horario Entrega Mañana:</strong> {{ horario($agenda->distribucion->desde, $agenda->distribucion->hasta) }} <br>
          <strong>Horario Entrega Tarde :</strong> {{ $agenda->distribucion->desde1 }} - {{
          $agenda->distribucion->hasta1
          }}
          <br>
          <strong>Lunes Cerrado:</strong> {{ $agenda->distribucion->lunes }} <br>
          <strong>Sabado Recibe:</strong> {{ $agenda->distribucion->sabado }} <br>
          <strong>Factura Impresa:</strong>
          <span
            style="background-color: {{ \App\Models\Distribucion::esSi($agenda->distribucion->fac_imp) ? 'red' : 'transparent' }}; padding: 2px 5px;">
            {{ $agenda->distribucion->fac_imp }}
          </span>
          <br>
          <strong>Obs.Recepción:</strong> {{ $agenda->distribucion->obsrecep }} <br>
          <hr class="bg-blue-900">
          {{-- @endforeach --}}
          <strong>Últimos Pedidos:</strong><br>
          <table class="table table-bordered table-sm text-xs">
            <thead>
              <tr>
                <th class=" text-center">Pedido</th>
                <th class=" text-center">Fecha Pedido</th>
                <th class=" text-center">Fecha Entrega</th>
                <th class=" text-center">Cantidad</th>
                <th>Producto</th>
                <th class=" text-right">Precio Unit</th>
                <th class=" text-right">Total</th>
              </tr>
            </thead>
            <tbody>
              @foreach($agenda->distribucion->distribucionLineaPedidos as $pedido)
              <tr>
                <td class=" text-center">{{ $pedido->pedido_id }}</td>
                <td class=" text-center">{{ fecha($pedido->fecha) }}</td>
                <td class=" text-center">{{ fecha($pedido->fechaEntrega) }}</td>
                <td class=" text-center">{{ $pedido->cantidad }}</td>
                <td>{{ $pedido->producto?->productoCDA ?? $pedido->producto_id }}</td>
                <td class=" text-right">${{ $pedido->precio_unitario }}</td>
                <td class=" text-right">${{ $pedido->totalLinea }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>
@stop

@section('js')
<script>
  function toggleText(id) {
    const shortText = document.getElementById(`temas${id}`);
    const fullText = document.getElementById(`full-temas${id}`);
    if (shortText.style.display === "none") {
      shortText.style.display = "inline";
      fullText.style.display = "none";
    } else {
      shortText.style.display = "none";
      fullText.style.display = "inline";
    }
  }
</script>
@stop

@include('partials.calendario-dias')
