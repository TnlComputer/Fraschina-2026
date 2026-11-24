<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Productos del cliente</title>
  <style>
    body { font-family: Arial, sans-serif; font-size: 10pt; }
    h1 { font-size: 14pt; text-align: center; margin-bottom: 4px; }
    p { text-align: center; margin: 0 0 12px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #666; padding: 4px 6px; }
    th { background: #ddd; }
    .r { text-align: right; }
    .c { text-align: center; }
  </style>
</head>
<body>
  <h1>Productos del cliente</h1>
  <p>{{ $cliente->id }} · {{ unir($cliente->nomfantasia, $cliente->razonsocial) }} · {{ fecha(now()) }}</p>
  <table>
    <thead>
      <tr><th>Producto</th><th>Precio</th><th>Fecha cotización</th></tr>
    </thead>
    <tbody>
      @forelse ($productos as $p)
      <tr>
        <td>{{ $p->producto?->nombre() ?? $p->nomproducto }}</td>
        <td class="r">{{ pesos($p->precio) }}</td>
        <td class="c">{{ fecha($p->fecha) }}</td>
      </tr>
      @empty
      <tr><td colspan="3" class="c">Sin productos cargados.</td></tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
