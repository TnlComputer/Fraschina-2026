<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Planilla de Reparto</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 8pt;
      color: #000;
      margin: 0;
      padding: 0;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 5px;
    }

    th,
    td {
      border: 1px solid #ddd;
      text-align: left;
      font-size: 9px;
    }

    th {
      background-color: #eaeaea;
      font-weight: bold;
    }

    .text-center {
      text-align: center;
    }

    .bold {
      font-weight: bold;
    }

    .footer {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      text-align: center;
      font-size: 10px;
    }

    @page {
      /* size: A4; */
      margin-bottom: 40px;
    }

    @page :last {
      @bottom-center {
        content: "Reporte generado automáticamente por el sistema.";
        font-size: 10px;
        color: #000;
      }
    }
  </style>
</head>

<body>
  @php
  $totalPorChofer = [];
  $totalPorProducto = [];
  @endphp

  <div class="header">
    <h1>Planilla de Reparto - Fecha de entrega: <strong>{{ \Carbon\Carbon::parse($fecha)->format('d-m-Y') }}</strong>
    </h1>
  </div>

  @foreach ($distribuciones as $distribucion)
  <table class="table table-bordered">
    <thead>
      <tr>
        <th class="text-center">Orden</th>
        <th class="text-center">Chofer</th>
        <th class="text-center">Pedido</th>
        {{-- <th>Línea</th> --}}
        <th>Nombre Fantasía</th>
        <th>Razón Social</th>
        <th>Dirección</th>
        <th>Barrio</th>
        <th>Municipio</th>
        <th>Localidad</th>
        <th>Zona</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="text-center">{{ $distribucion->orden }}</td>
        <td class="text-center">{{ $distribucion->chofer }}</td>
        <td class="text-center">{{ $distribucion->id }}</td>
        <td>{{ $distribucion->distribucion->nomfantasia }}</td>
        <td>{{ $distribucion->distribucion->razonsocial }}</td>
        <td>{{ $distribucion->distribucion->auxCalles->calle ?? '' }} {{ $distribucion->distribucion->dire_nro }}</td>
        <td>{{ $distribucion->distribucion->auxbarrio->nombrebarrio ?? '' }}</td>
        <td>{{ $distribucion->distribucion->auxmunicipio->ciudadmunicipio ?? '' }}</td>
        <td>{{ $distribucion->distribucion->auxlocalidad->localidad ?? '' }}</td>
        <td>{{ $distribucion->distribucion->auxzona->nombre ?? '' }}</td>
      </tr>
    </tbody>
  </table>

  <table class="table table-sm table-striped">
    <tr>
      <th class="text-center">Factura</th>
      <th class="text-center">Cobrar</th>
      <th class="text-center">Cond. Pago</th>
      <th class="text-center">Forma Pago</th>
      <th class="text-center">Horario Mañana</th>
      <th class="text-center">Horario Tarde</th>
      <th class="text-center">Lunes Cerrado</th>
      <th class="text-center">Sábado Recibe</th>
      <th class="text-rigth">Importe Factura</th>
    </tr>
    <tr>
      <td class="text-center"><strong>{{ \App\Models\Distribucion::esSi($distribucion->distribucion->fac_imp) ? 'IMPRIMIR' : '' }}</strong></td>
      <td class="text-center">{{ $distribucion->distribucion->auxcobro->accion ?? '' }}</td>
      <td class="text-center">{{ $distribucion->distribucion->auxpago->nombre ?? '' }}</td>
      <td class="text-center">{{ $distribucion->distribucion->auxtcobro->nombre ?? '' }}</td>
      {{-- Horarios guardados como H:i:s; vacíos o 00:00:00 no se muestran --}}
      <td class="text-center">{{ substr($distribucion->distribucion->desde ?? '', 0, 5) }} -
        {{ substr($distribucion->distribucion->hasta ?? '', 0, 5) }}</td>
      @if (empty($distribucion->distribucion->desde1) || $distribucion->distribucion->desde1 == '00:00:00')
      <td> </td>
      @else
      <td>
        {{ substr($distribucion->distribucion->desde1, 0, 5) }} -
        {{ substr($distribucion->distribucion->hasta1 ?? '', 0, 5) }}
      </td>
      @endif
      <td class="text-center">{{ \App\Models\Distribucion::esSi($distribucion->distribucion->sabado) ? 'Sí' : 'No' }}</td>
      <td class="text-center">{{ \App\Models\Distribucion::esSi($distribucion->distribucion->lunes) ? 'Sí' : 'No' }}</td>
      <td class="text-rigth">${{ number_format($distribucion->totalFactura, 2) }}</td>
  </table>
  <table class="table" style="padding-left:70px;">
    @if ($distribucion->tipo == 'P' || $distribucion->tipo == 'PT')
    <thead>
      <th class="text-center" style="width: 80px">Observaciones :</th>
      <td style="max-width: 100%">{{ $distribucion->distribucion->obsrecep ?? 'Sin observaciones' }}</td>
    </thead>
    @endif

    @if ($distribucion->tipo == 'T' || $distribucion->tipo == 'PT')
    @foreach($distribucion->lineasTareas as $lineaTarea)
    <thead>
      <th class="text-center" style="width: 80px">Tarea :</th>
      <td style="max-width: 100%">{{ $lineaTarea->tarea->tarea ?? 'Sin tareas' }}</td>
    </thead>
    @endforeach
    @endif
  </table>

  @if ($distribucion->tipo == 'P' || $distribucion->tipo == 'PT')
  <table class="table" style="width: 230px">
    <thead>
      <tr>
        <th>Producto</th>
        <th class="text-center">Cantidad</th>
        <th class="text-right">Precio Unitario</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($distribucion->lineasPedidos as $linea)
      <tr>
        <td>{{ $linea->producto->productoCDA }}</td>
        <td class="text-center">{{ $linea->cantidad }}</td>
        <td class="text-right">$ {{ number_format($linea->precio_unitario, 2) }}</td>
      </tr>
      @php
      $totalPorChofer[$distribucion->chofer] = ($totalPorChofer[$distribucion->chofer] ?? 0) + $linea->cantidad;
      $totalPorProducto[$linea->producto->productoCDA] = ($totalPorProducto[$linea->producto->productoCDA] ?? 0) +
      $linea->cantidad;
      @endphp
      @endforeach
    </tbody>
  </table>
  @endif
  <hr>
  @endforeach

  @if ($distribuciones->isNotEmpty())
  <div>
    <h3>Total por Chofer:</h3>
    <table class=" table" style="width: 200px">
      <thead>
        <tr>
          <th style="width: 50%">Chofer</th>
          <th class="text-center">Total Cantidad</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($totalPorChofer as $chofer => $totalCantidad)
        <tr>
          <td style="width: 100px">{{ $chofer }}</td>
          <td class="text-center">{{ $totalCantidad }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <br>
    <table class="table" style="width: 200px">
      <thead>
        <tr>
          <th style="width: 100px">Producto</th>
          <th class="text-center">Total Cantidad</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($totalPorProducto as $producto => $totalCantidad)
        <tr>
          <td>{{ $producto }}</td>
          <td class="text-center">{{ $totalCantidad }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif

  <div class="footer text-center">
    <p>Reporte generado automáticamente por el sistema.</p>
  </div>
</body>

</html>