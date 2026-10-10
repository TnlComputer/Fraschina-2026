@php
$colores = [
  'pendiente_confirmacion' => 'info',
  'confirmado' => 'success',
  'pendiente_cierre' => 'primary',
  'cerrado' => 'secondary',
  'anulado' => 'danger',
];
@endphp
<span class="badge badge-{{ $colores[$pedido->estado] ?? 'light' }}">{{ $pedido->estado_nombre }}</span>
