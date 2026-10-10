<p>Estimados {{ $molino->contMolino ?: $molino->nombre }}:</p>

<p>
  @if ($turno === 'manana')
  Les consultamos por las entregas previstas para hoy {{ $fecha }}:
  @else
  Les consultamos por las cargas para las entregas de mañana {{ $fecha }}:
  @endif
</p>

<table cellpadding="4" cellspacing="0" border="1" style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 13px;">
  <tr style="background: #eee"><th>Pedido</th><th>Cliente</th><th>Calidad</th><th>Envase</th><th>Hora</th><th>Entregar</th></tr>
  @foreach ($lineas as $l)
  <tr>
    <td>{{ $l->pedido->codigo }}</td>
    <td>{{ $l->pedido->cliente->nombre }}</td>
    <td>{{ $l->calidad }}</td>
    <td>{{ $l->envase }}</td>
    <td>{{ $l->hora }}</td>
    <td>{{ $l->lugar_entrega }}</td>
  </tr>
  @endforeach
</table>

<p>¿Nos confirman horario de carga y transporte? Muchas gracias.<br>Fraschina S.R.L.</p>
