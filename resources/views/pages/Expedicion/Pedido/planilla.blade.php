<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>{{ $titulo }} {{ $pedido->codigo }}</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
    h1 { font-size: 18px; margin: 0 0 4px 0; }
    .encabezado { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    .encabezado td { padding: 2px 6px; vertical-align: top; }
    .tabla { width: 100%; border-collapse: collapse; }
    .tabla th, .tabla td { border: 1px solid #444; padding: 4px; }
    .tabla th { background: #e8e8e8; font-size: 10px; text-transform: uppercase; }
    .c { text-align: center; }
    .subrayado { text-decoration: underline; }
    .tentativa { color: #1d4ed8; }
    .nota { font-size: 10px; color: #333; }
    .pie { margin-top: 12px; }
    .cambios { margin-top: 10px; font-size: 10px; }
    .marca { font-size: 9px; color: #666; margin-top: 16px; }
  </style>
</head>
<body>
  <table class="encabezado">
    <tr>
      <td style="width: 50%">
        <h1>{{ $titulo }}</h1>
        <strong>VENDEDOR: FRASCHINA S.R.L.</strong>
      </td>
      <td style="width: 50%; text-align: right">
        <strong>Pedido N° {{ $pedido->codigo }}</strong>
        @if ($pedido->revision > 0) <br><strong>MODIFICACIÓN</strong> @endif
        @if ($pedido->tipo === 'muestra') <br><strong>MUESTRA SIN CARGO</strong> @endif
        <br>Fecha: {{ now()->format('d/m/Y') }}
      </td>
    </tr>
    <tr>
      <td><strong>Molino:</strong> {{ $pedido->molino->nombre }}</td>
      <td style="text-align: right">@if ($pedido->molino->contMolino)<strong>Att.:</strong> {{ $pedido->molino->contMolino }}@endif</td>
    </tr>
  </table>

  <table class="tabla">
    <thead>
      <tr>
        <th>Cliente</th>
        <th>Tolvas</th>
        <th>Bolsas</th>
        <th>Fecha</th>
        <th>Calidad</th>
        @if ($mostrarPrecio)<th>Precio</th>@endif
        <th>Condición</th>
        <th>Planta</th>
        <th>Hora</th>
        <th>Plazo / Pago</th>
        <th>Observaciones</th>
        <th>Entregar</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($pedido->lineas as $l)
      <tr>
        <td>{{ $pedido->cliente->nombre }}</td>
        <td class="c">{{ $l->tolvas ?: '' }}</td>
        <td class="c">{{ $l->bolsas ?: '' }}</td>
        <td class="c {{ $l->fecha_tentativa ? 'tentativa' : '' }}">
          {{ $l->fecha_entrega?->format('d/m/Y') ?? 'A confirmar' }}
          @if ($l->fecha_tentativa)<div class="nota">(a confirmar)</div>@endif
        </td>
        <td>{{ $l->calidad }}</td>
        @if ($mostrarPrecio)
        <td class="{{ $subrayarPrecio ? 'subrayado' : '' }}">
          {{ $l->precio !== null ? '$' . number_format((float) $l->precio, 3, ',', '.') . ' + IVA' : '' }}
        </td>
        @endif
        <td>{{ $l->condicion }}</td>
        <td>{{ $l->planta }}</td>
        <td class="c">{{ $l->hora }}</td>
        <td>{{ $l->plazo_dias !== null ? $l->plazo_dias . ' días' : '' }}{{ $l->forma_pago ? ' · ' . $l->forma_pago : '' }}</td>
        <td>
          @if ($mostrarEspeciales){{ $l->observaciones }}@endif
          @if ($l->nota_fecha)<div class="nota">{{ $l->nota_fecha }}</div>@endif
        </td>
        <td>{{ $l->lugar_entrega }}</td>
      </tr>
      @if ($mostrarFechaAlternativa && $l->fecha_alternativa)
      <tr><td colspan="{{ $mostrarPrecio ? 12 : 11 }}" class="nota">Fecha alternativa línea {{ $l->linea }}: {{ $l->fecha_alternativa->format('d/m/Y') }}</td></tr>
      @endif
      @endforeach
    </tbody>
  </table>

  @if ($anterior)
  <div class="cambios">
    <strong>Cambios respecto de {{ $anterior->codigo }}:</strong>
    @foreach ($pedido->lineas as $l)
      @php $previa = $anterior->lineas->firstWhere('linea', $l->linea); @endphp
      @if ($previa && $previa->fecha_entrega?->format('Y-m-d') !== $l->fecha_entrega?->format('Y-m-d'))
      <div>Línea {{ $l->linea }}: tenía fecha {{ $previa->fecha_entrega?->format('d/m/Y') ?? 'a confirmar' }}, pasa a {{ $l->fecha_entrega?->format('d/m/Y') ?? 'a confirmar' }}.</div>
      @endif
    @endforeach
  </div>
  @endif

  @if ($pedido->observaciones && $mostrarEspeciales)
  <div class="pie"><strong>Observaciones:</strong> {{ $pedido->observaciones }}</div>
  @endif

  @if (count($textos))
  <div class="pie">
    @foreach ($textos as $texto)
    <div>{{ $texto }}</div>
    @endforeach
    <p><strong>MUCHAS GRACIAS</strong></p>
  </div>
  @endif

  <div class="marca">Fraschina S.R.L. · {{ config('expedicion.mail_remitente') }}</div>
</body>
</html>
