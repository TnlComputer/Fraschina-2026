<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recibo Provisorio</title>

  <style>
    * {
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      font-size: 10pt;
    }

    .header {
      border-bottom: 2px solid black;
      padding-bottom: 10px;
      margin-bottom: 10px;
    }

    .header table {
      width: 100%;
      border-collapse: collapse;
    }

    .header .sector {
      font-size: 8pt;
    }

    .header .text-right {
      text-align: right;
    }

    .header .text-center {
      text-align: center;
      font-size: 6pt;
    }

    .header .sector-left small {
      font-size: 6pt;
    }

    .header .sector-right {
      font-size: 6pt;
      text-align: right;
    }

    .header .sector-right .recibo-titulo {
      font-size: 12pt;
    }

    .header .sector-right .fecha {
      font-size: 10pt;
    }

    .text-x {
      display: inline-block;
      background-color: white;
      color: black;
      font-weight: bold;
      font-size: 14pt;
      width: 20px;
      height: 20px;
      border: 2px solid black;
      text-align: center;
      line-height: 20px;
      margin-top: -10px;
      padding: 3px;
    }

    .table-recibo {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      text-align: center;
    }

    .table-recibo th,
    .table-recibo td {
      border: 1px solid black;
      padding: 8px;
      font-size: 8pt;
    }

    .total-amount {
      font-size: 12pt;
      font-weight: bold;
    }

    .amount-in-words {
      font-size: 10pt;
      font-style: italic;
      margin-top: 10px;
    }

    /* Flex container to align tables side by side */
    .flex-container {
      display: flex;
      flex-direction: row;
      justify-content: space-between;
      align-items: flex-start;
      width: 100%;
      gap: 20px;
    }

    .table-recibo {
      width: 100%;
      table-layout: fixed;
    }

    .letra-cliente {
      font-size: 8pt;
      text-transform: uppercase;
      font-weight: bold;
    }

    .tabla-derecha {
      text-align: right;
    }

    .derecha {
      width: 58%;
      float: left;
      margin-right: 4%;
    }

    .izquierda {
      width: 38%;
      float: left;
      margin-right: 4%;
    }

    .importe_letras {
      margin: 0;
      padding: 0;
      text-align: left;
      font-size: 8pt
    }

    .container-firma {
      display: flex;
      justify-content: space-between;
      /* Distribuye el espacio entre los divs */
      align-items: flex-start;
      /* Alinea verticalmente los elementos al principio */
    }

    .left {
      text-align: left;
      /* Alinea el texto a la izquierda */
      margin-top: 30px;
      /* Ajusta la posición de "ORIGINAL" para que esté un poco más abajo */
    }

    .right {
      text-align: right;
      padding-right: 80px;
      /* Alinea el texto a la derecha */
    }

    .left p,
    .right p {
      margin: 0;
      /* Elimina márgenes por defecto que podrían interferir con la alineación */
    }

    .separacion {
      padding-top: 95px;
    }

    */ .clearfix::after {
      content: "";
      display: table;
      clear: both;
    }
  </style>
</head>

<body>
  <div class="header">
    <table>
      <tr>
        <td class="sector-left" style="width: 33%">
          <strong>FRASCHINA SRL</strong><br>
          <small>RAMÓN L. FALCÓN 2364 - Piso 1° Dpto "B"<br>
            (1406) - CIUDAD AUT.DE BUENOS AIRES<br>
            CEL.: 11-5955-7937<br>
            TEL.: 4637-4444<br>
            IVA Responsable Inscripto<br></small>
        </td>
        <td class="sector text-center" style="width: 33%">
          <span class="text-x">X</span><br><br>
          <small><strong>DOCUMENTO<br>
              NO VALIDO<br>
              COMO FACTURA</strong></small><br>
        </td>
        <td class="sector-right" style="width: 33%">
          <strong class="recibo-titulo">RECIBO PROVISORIO</strong><br>
          <strong class="fecha">NRO.: 0002-000016488</strong><br>
          <strong class="fecha">Fecha: 08-01-2025</strong><br>
          C.U.I.T.: 30-64021320-1<br>
          Ingresos Brutos: 771176-10<br>
          Inicio actividades: 01/05/1990
        </td>
      </tr>
    </table>
  </div>

  <div class="clearfix">
    <div class="izquierda">
      <table class="table-recibo">
        <thead>
          <tr>
            <th colspan="3">LIQUIDACION</th>
          </tr>
          <tr>
            <th>Fecha</th>
            <th>Factura</th>
            <th>Importe</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>{{ fecha($pedido->fechaFactura) }}</td>
            <td>{{ $pedido->nroFactura }}</td>
            <td>{{ number_format($pedido->totalFactura, 2, ',', '.') }}</td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td colspan="2" class="tabla-derecha">Total</td>
            <td>${{ number_format($pedido->totalFactura, 2, ',', '.') }}</td>
          </tr>
          {{-- <tr>
            <td colspan="3"></td>
          </tr> --}}
          <tr>
            <td colspan="2" class="tabla-derecha">Importe Neto</td>
            <td>${{ number_format($pedido->totalFactura, 2, ',', '.') }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="derecha">
      <strong class="letra-cliente">Recibimos de : {{ unir($pedido->razonsocial, $pedido->nomfantasia) }}</strong>

      <table class="table-recibo">
        <thead>
          <tr>
            <th>Banco</th>
            <th>Cheque</th>
            <th>Fecha</th>
            <th>Importe</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td colspan="3" class="tabla-derecha">Total</td>
            <td></td>
          </tr>
          <tr>
            <td colspan="3" class="tabla-derecha">Importe Neto</td>
            <td></td>
          </tr>
        </tbody>
      </table>
      {{-- <div class="amount-in-words"> --}}
        <span class="letra-cliente"> {{ $importeTexto }} </span>
        {{--
      </div> --}}
    </div>
  </div>
  <div class="clearfix">
    <div class="container-firma">
      <div class="left">
        <p>ORIGINAL</p>
      </div>
      <div class="right">
        <p>____________</p>
        <p>Fraschina SRL</p>
      </div>
    </div>
  </div>

  {{-- DUPLICADO --}}
  <div class="header separacion">
    <table>
      <tr>
        <td class="sector-left" style="width: 33%">
          <strong>FRASCHINA SRL</strong><br>
          <small>RAMÓN L. FALCÓN 2364 - Piso 1° Dpto "B"<br>
            (1406) - CIUDAD AUT.DE BUENOS AIRES<br>
            CEL.: 11-5955-7937<br>
            TEL.: 4637-4444<br>
            IVA Responsable Inscripto<br></small>
        </td>
        <td class="sector text-center" style="width: 33%">
          <span class="text-x">X</span><br><br>
          <small><strong>DOCUMENTO<br>
              NO VALIDO<br>
              COMO FACTURA</strong></small><br>
        </td>
        <td class="sector-right" style="width: 33%">
          <strong class="recibo-titulo">RECIBO PROVISORIO</strong><br>
          <strong class="fecha">NRO.: 0002-000016488</strong><br>
          <strong class="fecha">Fecha: 08-01-2025</strong><br>
          C.U.I.T.: 30-64021320-1<br>
          Ingresos Brutos: 771176-10<br>
          Inicio actividades: 01/05/1990
        </td>
      </tr>
    </table>
  </div>

  <div class="clearfix">
    <div class="izquierda">
      <table class="table-recibo">
        <thead>
          <tr>
            <th colspan="3">LIQUIDACION</th>
          </tr>
          <tr>
            <th>Fecha</th>
            <th>Factura</th>
            <th>Importe</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>{{ fecha($pedido->fechaFactura) }}</td>
            <td>{{ $pedido->nroFactura }}</td>
            <td>{{ number_format($pedido->totalFactura, 2, ',', '.') }}</td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td colspan="2" class="tabla-derecha">Total</td>
            <td>${{ number_format($pedido->totalFactura, 2, ',', '.') }}</td>
          </tr>
          {{-- <tr>
            <td colspan="3"></td>
          </tr> --}}
          <tr>
            <td colspan="2" class="tabla-derecha">Importe Neto</td>
            <td>${{ number_format($pedido->totalFactura, 2, ',', '.') }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="derecha">
      <strong class="letra-cliente">Recibimos de : {{ unir($pedido->razonsocial, $pedido->nomfantasia) }}</strong>

      <table class="table-recibo">
        <thead>
          <tr>
            <th>Banco</th>
            <th>Cheque</th>
            <th>Fecha</th>
            <th>Importe</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td colspan="3" class="tabla-derecha">Total</td>
            <td></td>
          </tr>
          <tr>
            <td colspan="3" class="tabla-derecha">Importe Neto</td>
            <td></td>
          </tr>
        </tbody>
      </table>
      {{-- <div class="amount-in-words"> --}}
        <span class="letra-cliente"><strong> {{ $importeTexto }} </strong></span>
        {{--
      </div> --}}
    </div>
  </div>
  <div class="clearfix">
    <div class="container-firma">
      <div class="left">
        <p>DUPLICADO</p>
      </div>
      <div class="right">
        <p>____________</p>
        <p>Fraschina SRL</p>
      </div>
    </div>
  </div>
</body>

</html>