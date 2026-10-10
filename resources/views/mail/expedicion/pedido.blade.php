<p>Estimados {{ $pedido->molino->contMolino ?: $pedido->molino->nombre }}:</p>

<p>
  Les enviamos {{ $pedido->revision > 0 ? 'la modificación del pedido' : 'el pedido' }}
  <strong>N° {{ $pedido->codigo }}</strong> para el cliente <strong>{{ $pedido->cliente->nombre }}</strong>.
  Se adjunta la {{ mb_strtolower(config('expedicion.nombre_planilla')) }}.
</p>

<table cellpadding="4" cellspacing="0" border="1" style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 13px;">
  <tr style="background: #eee"><th>Calidad</th><th>Envase</th><th>Fecha</th><th>Hora</th><th>Entregar</th></tr>
  @foreach ($pedido->lineas as $l)
  <tr>
    <td>{{ $l->calidad }}</td>
    <td>{{ $l->envase }}</td>
    <td>{{ $l->fecha_entrega?->format('d/m/Y') ?? 'A confirmar' }}{{ $l->fecha_tentativa ? ' (a confirmar)' : '' }}</td>
    <td>{{ $l->hora }}</td>
    <td>{{ $l->lugar_entrega }}</td>
  </tr>
  @endforeach
</table>

<p>Muchas gracias.<br>Fraschina S.R.L.</p>
