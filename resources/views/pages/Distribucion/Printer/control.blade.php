<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Control de Recaudación</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 10pt;
      color: #000;
      box-sizing: border-box;
    }

    .title {
      text-align: center;
      font-size: 14pt;
      font-weight: bold;
      margin-bottom: 10px;
    }

    table {
      border-collapse: collapse;
    }

    .tabla-cobro-dia {
      width: 70%;
      margin-right: 0;
    }

    .tabla-cobro {
      width: 100%;

    }

    .tabla-gastos {
      width: 100%;
    }

    .tabla-firmas {
      width: 100%;
      text-align: center;
    }

    .tabla-cobro-td {
      width: 40px;
    }

    th,
    td {
      border: 1px solid #000;
      text-align: center;
      font-size: 9px;
      padding: 2px;
      height: 15px;
    }

    th {
      background-color: #eaeaea;
      font-weight: bold;
    }

    td.t-izquierda {
      text-align: left;
    }

    .subtotal,
    .total {
      font-weight: bold;
    }

    .signature-section {
      margin-top: 30px;
      text-align: center;
    }

    .signature-box {
      display: inline-block;
      width: 30%;
      border-top: 1px solid #000;
      padding-top: 5px;
      font-weight: bold;
    }

    .footer {
      margin-top: 20px;
      text-align: center;
      font-size: 8pt;
    }

    @page {

      @bottom-right {
        content: 'Página ' counter(page) ' de ' counter(pages);
        font-size: 10px;
        font-family: Arial, sans-serif;
      }
    }
  </style>
</head>

<body>
  <div class="title">
    CONTROL RECAUDACIÓN : {{ fecha($fecha) }}
    {{-- <span style="float: right;">Página 1/1</span> --}}
  </div>
  <table style="width: 378px; margin-left: 325px;">
    <thead>
      <tr>
        <th style="width: 174px">Facturas del Día</th>
        <th style="width: 122px">Cobrado en el Día</th>
      </tr>
    </thead>
  </table>
  <table class="tabla-cobro">
    <thead>
      <tr>
        <th style="width: 120px">Nombre de Fantasía</th>
        <th style="width: 150px">Razón Social</th>
        <th class="tabla-cobro-td">C.Pago</th>
        <th class="tabla-cobro-td">Importe</th>
        <th class="tabla-cobro-td">Factura</th>
        <th class="tabla-cobro-td">Recibo</th>
        <th class="tabla-cobro-td">Factura</th>
        <th class="tabla-cobro-td">Importe</th>
        <th class="tabla-cobro-td">Recibo</th>
      </tr>
    </thead>
    @foreach ($distribuciones as $distribucion)
    <tbody>
      <tr>
        <td class="t-izquierda">{{ $distribucion->distribucion->nomfantasia }}</td>
        <td class="t-izquierda">{{ $distribucion->distribucion->razonsocial }} </td>
        <td">{{ $distribucion->distribucion->auxpago->nombre ?? '' }}</td>
          <td class="text-rigth">${{ numero($distribucion->totalFactura, 2) }}</td>
          <td>{{ $distribucion->nroFactura }}</td>
          <td>{{ $distribucion->id }}</td>
          <td></td>
          <td></td>
          <td></td>
      </tr>
      @endforeach
      @php
      $lin = $distribuciones->count();
      $gen = 25 - $lin;
      @endphp
      @for ($x = 0; $x < $gen; $x++) <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        </tr>
        @endfor
    </tbody>
  </table>
  <table style="width: 90px; margin-left:543px;">
    <thead>
      <tr>
        <th style="width: 40px">SUBTOTAL</th>
        <td style="width: 48px"></td>
      </tr>
    </thead>
  </table>

  <br>

  <table class="tabla-gastos">
    <thead>
      <tr style="background-color: #a9a9a9; font-weight: bold;">
        <th style="width: 530px">Gastos</th>
        <th style="width: 42px">Importe</th>
        <th style="width: 42px">Compr.</th>
      </tr>
    </thead>
    <tbody>
      @for ($i = 0; $i < 6; $i++) <tr>
        <td></td>
        <td></td>
        <td></td>
        </tr>
        @endfor
    </tbody>
  </table>
  </table>
  <table style="width: 90px; margin-left:543px;">
    <thead>
      <tr>
        <th style="width: 40px">SUBTOTAL</th>
        <td style="width: 48px"></td>
      </tr>
    </thead>
  </table>
  <br>
  </table>
  <table style="width: 150px; margin-left:502px;">
    <thead>
      <tr>
        <th style="width: 80px">TOTAL RENDIDO</th>
        <td style="width: 44px"></td>
      </tr>
    </thead>
  </table>
  <br>
  <table style="width: 150px; margin-left:502px;">
    <thead>
      <tr>
        <th style="width: 80px">PRECINTO NRO.</th>
        <td style="width: 44px"></td>
      </tr>
    </thead>
  </table>
  <br>
  <table class="tabla-firmas">
    <tr>
      <td style="width: 230px">RENDICIÓN REPARTO (Firma y Fecha)</td>
      <td style="width: 230px">CONTROL (Firma y Fecha) </td>
      <td style="width: 230px">RENDICIÓN OFICINA (Firma y Fecha)</td>
    </tr>
    <tr>
      <td style="height: 80px; border: 1px solid #000;"></td>
      <td style="border: 1px solid #000;"></td>
      <td style="border: 1px solid #000;"></td>
    </tr>
  </table>

  <div class="footer">
    Reporte generado automáticamente por el sistema.
  </div>
</body>

</html>