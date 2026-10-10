{{-- Tabla compacta de pedidos. Recibe $lista y $vacio. --}}
<table class="table table-sm table-striped mb-0">
  <thead>
    <tr><th>Pedido</th><th>Fecha</th><th>Molino</th><th>Cliente</th><th>Detalle</th><th></th></tr>
  </thead>
  <tbody>
    @forelse ($lista as $p)
    <tr>
      <td>
        <a href="{{ route('expedicion_pedido.show', $p) }}"><strong>{{ $p->codigo }}</strong></a>
        @if ($p->tipo === 'muestra') <span class="badge badge-secondary">Muestra</span> @endif
      </td>
      <td>{{ $p->fecha_pedido?->format('d/m/Y') }}</td>
      <td>{{ $p->molino->sigla }}</td>
      <td>{{ $p->cliente->sigla }}</td>
      <td class="small">
        @foreach ($p->lineas as $l)
        <div class="{{ $l->fecha_tentativa ? 'text-primary' : '' }}">
          {{ $l->calidad }} · {{ $l->envase }} · {{ $l->fecha_entrega?->format('d/m') ?? 'a confirmar' }}
        </div>
        @endforeach
      </td>
      <td class="text-right">
        @if ($p->esPendiente())
        <form action="{{ route('expedicion_pedido.confirmar', $p) }}" method="POST" class="d-inline">
          @csrf
          <button class="btn btn-xs btn-success" title="Confirmar">Confirmar</button>
        </form>
        @endif
      </td>
    </tr>
    @empty
    <tr><td colspan="6" class="text-muted text-center">{{ $vacio }}</td></tr>
    @endforelse
  </tbody>
</table>
