@extends('adminlte::page')

@section('title', 'Pedido ' . $pedido->codigo)

@section('content_header')
<div class="d-flex flex-wrap justify-content-between align-items-center">
  <h1>
    Pedido {{ $pedido->codigo }}
    @include('pages.Expedicion.partials.estado', ['pedido' => $pedido])
    @if ($pedido->tipo === 'muestra') <span class="badge badge-secondary">Muestra sin cargo</span> @endif
    @if (!$pedido->vigente) <span class="badge badge-light">Revisión reemplazada</span> @endif
  </h1>
  <div>
    <a href="{{ route('expedicion_pedido.index') }}" class="btn btn-secondary btn-sm">Volver</a>
  </div>
</div>
@endsection

@section('content')
<div class="row">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-md-4"><strong>Cliente:</strong> {{ $pedido->cliente->nombre }}</div>
          <div class="col-md-4"><strong>Molino:</strong> {{ $pedido->molino->nombre }}</div>
          <div class="col-md-4"><strong>Fecha:</strong> {{ $pedido->fecha_pedido?->format('d/m/Y') }}</div>
        </div>
        <div class="row mt-1">
          <div class="col-md-4"><strong>Cargado por:</strong> {{ $pedido->usuario?->name ?? '-' }}</div>
          <div class="col-md-8"><strong>Enviado al molino:</strong>
            {{ $pedido->enviado_at ? $pedido->enviado_at->format('d/m/Y H:i') . ' (' . basename($pedido->archivo) . ')' : 'No' }}
          </div>
        </div>
        @if ($pedido->observaciones)
        <div class="mt-2"><strong>Observaciones:</strong> {{ $pedido->observaciones }}</div>
        @endif
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h3 class="card-title">Líneas</h3></div>
      <div class="card-body p-0 table-responsive">
        <table class="table table-sm table-bordered mb-0">
          <thead class="thead-light">
            <tr>
              <th>#</th><th>Calidad</th><th>Envase</th><th>Kilos</th><th>Precio</th><th>Condición</th><th>Planta</th>
              <th>Entrega</th><th>Hora</th><th>Plazo</th><th>Forma de pago</th><th>Descarga</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($pedido->lineas as $l)
            <tr class="{{ $l->fecha_tentativa ? 'text-primary' : '' }}">
              <td>{{ $l->linea }}</td>
              <td>{{ $l->calidad }}</td>
              <td>{{ $l->envase }}</td>
              <td>{{ $l->kilos ? number_format($l->kilos, 0, ',', '.') : '' }}</td>
              <td>{{ $l->precio !== null ? '$' . number_format((float) $l->precio, 3, ',', '.') : '' }}</td>
              <td>{{ $l->condicion }}</td>
              <td>{{ $l->planta }}</td>
              <td>
                {{ $l->fecha_entrega?->format('d/m/Y') ?? 'A confirmar' }}
                @if ($l->fecha_tentativa) <small>(tentativa)</small> @endif
                @if ($l->nota_fecha) <div class="small">{{ $l->nota_fecha }}</div> @endif
                @if ($l->fecha_alternativa) <div class="small text-muted">Alt.: {{ $l->fecha_alternativa->format('d/m/Y') }}</div> @endif
              </td>
              <td>{{ $l->hora }}</td>
              <td>{{ $l->plazo_dias !== null ? $l->plazo_dias . ' días' : '' }}</td>
              <td>{{ $l->forma_pago }}</td>
              <td>{!! $l->entrega?->descarga_completa ? '<i class="fas fa-check text-success"></i>' : '' !!}</td>
            </tr>
            @if ($l->lugar_entrega || $l->observaciones)
            <tr class="small text-muted"><td></td><td colspan="11">
              @if ($l->lugar_entrega) Entregar en: {{ $l->lugar_entrega }}. @endif {{ $l->observaciones }}
            </td></tr>
            @endif
            @endforeach
          </tbody>
          <tfoot>
            <tr><th colspan="2" class="text-right">Totales</th><th colspan="10">{{ $pedido->totalTolvas() }} tolvas · {{ $pedido->totalBolsas() }} bolsas</th></tr>
          </tfoot>
        </table>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h3 class="card-title">Historial (Diaria)</h3></div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <tbody>
            @foreach ($pedido->movimientos as $mov)
            <tr>
              <td class="text-nowrap small">{{ $mov->created_at?->format('d/m/Y H:i') }}</td>
              <td><span class="badge badge-light">{{ $mov->accion_nombre }}</span></td>
              <td class="small" style="white-space: pre-line">{{ $mov->detalle }}</td>
              <td class="small text-muted">{{ $mov->usuario?->name }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card card-primary card-outline">
      <div class="card-header"><h3 class="card-title">Acciones</h3></div>
      <div class="card-body">
        @if ($pedido->esPendiente() && $pedido->vigente)
        <form action="{{ route('expedicion_pedido.confirmar', $pedido) }}" method="POST" class="mb-2">
          @csrf
          <button class="btn btn-success btn-block">Confirmar y asignar número</button>
        </form>
        @endif

        @if ($pedido->esEditable())
        <a href="{{ route('expedicion_pedido.edit', $pedido) }}" class="btn btn-warning btn-block mb-2">
          {{ $pedido->enviado_at ? 'Modificar (crea revisión ' . $pedido->numero . '-' . ($pedido->revision + 1) . ')' : 'Editar' }}
        </a>
        @endif

        @if ($pedido->numero)
        <a href="{{ route('expedicion_pedido.planilla', $pedido) }}" target="_blank" class="btn btn-outline-secondary btn-block mb-2">
          <i class="fas fa-file-pdf"></i> Ver {{ config('expedicion.nombre_planilla') }}
        </a>
        @endif

        @if ($pedido->numero && $pedido->vigente && $pedido->estado !== 'anulado')
        <form action="{{ route('expedicion_pedido.enviar', $pedido) }}" method="POST" class="mb-2"
          onsubmit="return confirm('¿Enviar la planilla al molino?');">
          @csrf
          <button class="btn btn-primary btn-block"><i class="fas fa-paper-plane"></i> {{ $pedido->enviado_at ? 'Reenviar' : 'Enviar' }} al molino</button>
        </form>
        <p class="small text-muted mb-3">
          Destinatarios:
          {{ $destinatarios->isNotEmpty() ? $destinatarios->pluck('destino')->unique()->implode(', ') : 'ninguno configurado' }}
          — <a href="{{ route('expedicion_avisos.index') }}">configurar</a>
        </p>
        @endif

        @if ($pedido->vigente && !in_array($pedido->estado, ['pendiente_confirmacion', 'anulado']))
        <form action="{{ route('expedicion_pedido.estado', $pedido) }}" method="POST">
          @csrf
          <label class="small mb-0">Cambiar estado</label>
          <div class="input-group input-group-sm">
            <select name="estado" class="form-control">
              @foreach (\App\Models\ExpPedido::ESTADOS as $clave => $nombre)
              @continue($clave === 'pendiente_confirmacion')
              <option value="{{ $clave }}" @selected($pedido->estado === $clave)>{{ $nombre }}</option>
              @endforeach
            </select>
            <div class="input-group-append"><button class="btn btn-outline-primary">Aplicar</button></div>
          </div>
          <input type="text" name="motivo" class="form-control form-control-sm mt-1" placeholder="Motivo (opcional)" maxlength="255">
        </form>
        @endif

        @if ($pedido->esPendiente() && $pedido->vigente)
        <form action="{{ route('expedicion_pedido.destroy', $pedido) }}" method="POST" class="mt-3"
          onsubmit="return confirm('¿Descartar este pedido pendiente?');">
          @csrf @method('DELETE')
          <button class="btn btn-outline-danger btn-block btn-sm">Descartar pedido pendiente</button>
        </form>
        @endif
      </div>
    </div>

    @if ($revisiones->count() > 1)
    <div class="card">
      <div class="card-header"><h3 class="card-title">Revisiones</h3></div>
      <ul class="list-group list-group-flush">
        @foreach ($revisiones as $rev)
        <li class="list-group-item d-flex justify-content-between {{ $rev->id === $pedido->id ? 'active' : '' }}">
          <a href="{{ route('expedicion_pedido.show', $rev) }}" class="{{ $rev->id === $pedido->id ? 'text-white' : '' }}">{{ $rev->codigo }}</a>
          <small>{{ $rev->created_at?->format('d/m/Y H:i') }} {{ $rev->vigente ? '(vigente)' : '' }}</small>
        </li>
        @endforeach
      </ul>
    </div>
    @endif
  </div>
</div>
@endsection
