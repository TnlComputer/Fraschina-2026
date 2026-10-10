@extends('adminlte::page')

@php
$editando = $pedido->exists;
$lineas = old('lineas', $editando ? $pedido->lineas->map(fn($l) => array_merge($l->only(\App\Models\ExpPedidoLinea::CAMPOS), [
  'fecha_entrega' => $l->fecha_entrega?->format('Y-m-d'),
  'fecha_alternativa' => $l->fecha_alternativa?->format('Y-m-d'),
  'precio' => $l->precio !== null ? (float) $l->precio : null,
]))->all() : [[]]);
@endphp

@section('title', $editando ? 'Editar pedido ' . $pedido->codigo : 'Nuevo pedido')

@section('content_header')
<h1>{{ $editando ? 'Editar pedido ' . $pedido->codigo : 'Nuevo pedido' }}</h1>
@if ($editando && $pedido->enviado_at)
<div class="alert alert-warning mt-2 mb-0">
  Este pedido ya se envió al molino el {{ $pedido->enviado_at->format('d/m/Y H:i') }}.
  Al guardar se crea la revisión <strong>{{ $pedido->numero }}-{{ $pedido->revision + 1 }}</strong> y la actual queda como histórico.
</div>
@endif
@endsection

@section('content')
<form method="POST" action="{{ $editando ? route('expedicion_pedido.update', $pedido) : route('expedicion_pedido.store') }}" id="form-pedido">
  @csrf
  @if ($editando) @method('PUT') @endif

  <div class="card">
    <div class="card-body">
      <div class="form-row">
        <div class="col-md-3">
          <label>Cliente</label>
          <select name="expedicion_cliente_id" id="cliente" class="form-control" required>
            <option value="">Seleccione</option>
            @foreach ($clientes as $c)
            <option value="{{ $c->id }}" @selected(old('expedicion_cliente_id', $pedido->expedicion_cliente_id) == $c->id)>{{ $c->nombre }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label>Molino</label>
          <select name="expedicion_molino_id" id="molino" class="form-control" required>
            <option value="">Seleccione</option>
            @foreach ($molinos as $m)
            <option value="{{ $m->id }}" @selected(old('expedicion_molino_id', $pedido->expedicion_molino_id) == $m->id)>{{ $m->nombre }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label>Fecha del pedido</label>
          <input type="date" name="fecha_pedido" class="form-control" required
            value="{{ old('fecha_pedido', $pedido->fecha_pedido?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
        </div>
        <div class="col-md-2">
          <label>Tipo</label>
          <select name="tipo" class="form-control">
            @foreach ($tipos as $clave => $nombre)
            <option value="{{ $clave }}" @selected(old('tipo', $pedido->tipo) === $clave)>{{ $nombre }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <small id="condiciones-info" class="text-muted"></small>
        </div>
      </div>
      <div class="form-group mt-2 mb-0">
        <label>Observaciones</label>
        <textarea name="observaciones" rows="2" class="form-control">{{ old('observaciones', $pedido->observaciones) }}</textarea>
      </div>
    </div>
  </div>

  <div id="lineas">
    @foreach ($lineas as $i => $l)
    @include('pages.Expedicion.Pedido.linea', ['i' => $i, 'l' => $l])
    @endforeach
  </div>

  <template id="linea-template">
    @include('pages.Expedicion.Pedido.linea', ['i' => '__I__', 'l' => []])
  </template>

  <button type="button" class="btn btn-outline-primary mb-3" id="agregar-linea"><i class="fas fa-plus"></i> Agregar línea</button>

  <div class="card">
    <div class="card-body d-flex flex-wrap align-items-center">
      @if (!$editando)
      <button type="submit" name="confirmar" value="0" class="btn btn-info mr-2">Guardar como pendiente de confirmación</button>
      <button type="submit" name="confirmar" value="1" class="btn btn-success mr-2">Guardar y confirmar</button>
      @else
      <button type="submit" class="btn btn-success mr-2">Guardar cambios</button>
      @endif
      <a href="{{ $editando ? route('expedicion_pedido.show', $pedido) : route('expedicion.index') }}" class="btn btn-secondary">Cancelar</a>
      <small class="text-muted ml-3">Las entregas con <strong>fecha tentativa</strong> se ven en azul en la planificación.</small>
    </div>
  </div>
</form>
@endsection

@section('js')
<script>
  (function () {
    const contenedor = document.getElementById('lineas');
    const template = document.getElementById('linea-template').innerHTML;
    let siguiente = {{ count($lineas) }};
    let condiciones = {};

    function renumerar() {
      contenedor.querySelectorAll('.linea-pedido').forEach((card, idx) => {
        card.querySelector('.numero-linea').textContent = idx + 1;
      });
    }

    // Completa las condiciones habituales del cliente-molino solo en campos vacíos
    function aplicarCondiciones(card) {
      ['plazo_dias', 'forma_pago', 'condicion', 'planta'].forEach(campo => {
        const input = card.querySelector(`[data-campo="${campo}"]`);
        if (input && !input.value && condiciones[campo] !== null && condiciones[campo] !== undefined) {
          input.value = condiciones[campo];
        }
      });
    }

    async function cargarCondiciones() {
      const cliente = document.getElementById('cliente').value;
      const molino = document.getElementById('molino').value;
      const info = document.getElementById('condiciones-info');
      if (!cliente || !molino) { info.textContent = ''; return; }
      try {
        const url = `{{ route('expedicion_pedido.condiciones') }}?cliente_id=${cliente}&molino_id=${molino}`;
        condiciones = await (await fetch(url, { headers: { 'Accept': 'application/json' } })).json();
        info.textContent = condiciones.configurado
          ? 'Condiciones habituales cargadas (se pueden cambiar).'
          : 'Esta combinación cliente-molino no tiene condiciones configuradas.';
        contenedor.querySelectorAll('.linea-pedido').forEach(aplicarCondiciones);
      } catch (e) {
        console.error(e);
      }
    }

    document.getElementById('agregar-linea').addEventListener('click', () => {
      const html = template.replaceAll('__I__', siguiente++);
      contenedor.insertAdjacentHTML('beforeend', html);
      const card = contenedor.lastElementChild;
      aplicarCondiciones(card);
      renumerar();
    });

    contenedor.addEventListener('click', e => {
      const boton = e.target.closest('.quitar-linea');
      if (!boton) return;
      if (contenedor.querySelectorAll('.linea-pedido').length === 1) {
        alert('El pedido necesita al menos una línea.');
        return;
      }
      boton.closest('.linea-pedido').remove();
      renumerar();
    });

    document.getElementById('cliente').addEventListener('change', cargarCondiciones);
    document.getElementById('molino').addEventListener('change', cargarCondiciones);
    if (!{{ $editando ? 'true' : 'false' }}) cargarCondiciones();
    renumerar();
  })();
</script>
@endsection
