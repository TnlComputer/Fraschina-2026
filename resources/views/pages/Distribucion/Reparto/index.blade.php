@extends('adminlte::page')

@section('title', 'Distribución')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/es.js"></script>
<style>
  /* Días con pedidos de reparto */
  .flatpickr-day.con-pedidos { font-weight: 800; color: #1f2d3d; }
  .flatpickr-day.con-pedidos::after {
    content: ''; position: absolute; left: 50%; bottom: 3px; transform: translateX(-50%);
    width: 4px; height: 4px; border-radius: 50%; background: #28a745;
  }
  .flatpickr-day.con-pedidos.selected::after { background: #fff; }
  .flatpickr-day { position: relative; }
</style>
<div class="container-fluid">
  <h1 class="reparto-titulo">Reparto <small class="text-muted">{{ ucfirst(\Carbon\Carbon::parse($fecha)->locale('es')->isoFormat('dddd D/MM/YYYY')) }} · {{ $distribuciones->count() }} pedidos</small></h1>

  <!-- Botones y fecha (en el celular se acomodan en dos renglones) -->
  <div class="mb-3 d-flex justify-content-between align-items-center reparto-barra">
    <div class="reparto-acciones">
      @can('permiso_11')<a href="{{ route('distribucion_pedido.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Pedido
      </a>@endcan
      <a href="{{ route('distribucion_reparto.movil', ['fecha' => $fecha]) }}" class="btn btn-outline-primary" title="Vista para el chofer (celular)"><i class="fas fa-mobile-screen"></i> Chofer</a>
      <a href="{{ route('distribucion_reparto.imprimirReparto', ['fecha' => $fecha]) }}" title="Impresión Reparto"
        target="_blank" class="btn btn-info"><i class="fas fa-print"></i>
        Reparto
      </a>
    {{-- <div>
      <a href="{{ route('distribucion_reparto.imprimirControl', ['fecha' => $fecha]) }}" title="Impresi´on Control"
        target="_blank" class="btn btn-secondary"><i class="fas fa-print"></i> Control
      </a>
    </div> --}}
      <a href="#"
        onclick="verificarDatos(event, '{{ route('distribucion_reparto.imprimirControl', ['fecha' => $fecha]) }}')"
        title="Impresión Control" class="btn btn-secondary">
        <i class="fas fa-print"></i> Control
      </a>
    </div>
    <!-- Formulario y Botones de Cambio de Fecha a la derecha -->
    <form method="GET" action="{{ route('distribucion_reparto.index') }}" id="fecha-form"
      class="d-flex align-items-center reparto-fechas">
      <!-- Campo de Fecha -->
      <input type="text" name="fecha" id="fecha-input" value="{{ $fecha }}" autocomplete="off"
        class="form-control d-inline-block w-auto h-100" style="height: calc(2.25rem + 2px); background: #fff;"
        title="Los días en negrita tienen pedidos">

      <!-- Botones de Cambio de Fecha -->
      <button type="submit" class="btn btn-success mx-1 h-100 d-none d-md-inline-block">Buscar por Fecha</button>
      <button type="button" class="btn btn-secondary mx-1 h-100" id="anterior-btn" title="Día Anterior"><i class="fas fa-chevron-left d-md-none"></i><span class="d-none d-md-inline">Día Anterior</span></button>
      <button type="button" class="btn btn-info mx-1 h-100" id="posterior-btn" title="Día Posterior"><i class="fas fa-chevron-right d-md-none"></i><span class="d-none d-md-inline">Día Posterior</span></button>
      <button type="button" class="btn btn-primary mx-1 h-100" id="hoy-btn">Hoy</button>
    </form>
  </div>

  {{-- Un formulario por pedido, fuera de la tabla: los campos de cada fila se asocian con form="..." --}}
  @foreach ($distribuciones as $distribucion)
  <form method="POST" action="{{ route('distribucion_reparto.update', $distribucion->id) }}" id="distribucion-form-{{ $distribucion->id }}">
    @csrf
    @method('PUT')
    <input type="hidden" name="fecha" value="{{ $fecha }}">
  </form>
  @endforeach

  {{-- Plantillas de los carteles que aparecen al pasar el mouse: productos (importe, lo usan para facturar) y entrega (orden) --}}
  <div class="d-none">
    @foreach ($distribuciones as $distribucion)
    @if ($distribucion->lineasPedidos->isNotEmpty())
    <div id="productos-pedido-{{ $distribucion->id }}">
      <table class="table table-sm table-bordered mb-0 text-xs">
        <thead>
          <tr><th>Producto</th><th class="text-center">Cant.</th><th class="text-right">P. Unit.</th><th class="text-right">Total c/IVA</th></tr>
        </thead>
        <tbody>
          @foreach ($distribucion->lineasPedidos as $linea)
          <tr>
            <td>
              {{ $linea->producto?->productoCDA }}
              @if (in_array(trim((string) $linea->cambiar), ['*', 'SI'], true)) <span class="badge badge-warning">SIN CARGO</span> @endif
              @if (in_array(trim((string) $linea->retirar), ['*', 'SI'], true)) <span class="badge badge-info">RETIRAR</span> @endif
            </td>
            <td class="text-center">{{ numero($linea->cantidad, 0) }}</td>
            <td class="text-right">{{ pesos($linea->precio_unitario) }}</td>
            <td class="text-right">{{ pesos($linea->totalLinea) }}</td>
          </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr><th colspan="3" class="text-right">Total pedido</th><th class="text-right">{{ pesos($distribucion->totalPedido) }}</th></tr>
        </tfoot>
      </table>
    </div>
    @endif
    {{-- Datos de entrega del cliente, para el cartel del campo Orden --}}
    @php $cli = $distribucion->distribucion; @endphp
    {{-- Ficha del cliente, para el cartel del nombre de fantasía y la razón social --}}
    <div id="cliente-pedido-{{ $distribucion->id }}">
      <table class="table table-sm table-bordered mb-0 text-xs">
        <tr><th>Nombre Fantasía</th><td class="popover-texto">{{ $cli?->nomfantasia }}</td></tr>
        <tr><th>Razón Social</th><td class="popover-texto">{{ $cli?->razonsocial }}</td></tr>
        <tr><th>Cliente</th><td>{{ $cli?->id }}</td></tr>
        <tr><th>CUIT</th><td>{{ $cli?->cuit ?: '—' }}</td></tr>
        <tr><th>Dirección</th><td class="popover-texto">{{ $cli?->auxCalles?->calle }} {{ $cli?->dire_nro }} {{ $cli?->piso }} {{ $cli?->dpto }}
          @if ($cli?->dire_obs)<br>{{ $cli->dire_obs }}@endif</td></tr>
        <tr><th>Barrio / Localidad</th><td class="popover-texto">{{ unir($cli?->auxbarrio?->nombrebarrio, $cli?->auxmunicipio?->ciudadmunicipio, $cli?->auxlocalidad?->localidad) ?: '—' }}</td></tr>
        <tr><th>Zona</th><td>{{ trim((string) $cli?->auxzona?->nombre) ?: '—' }}</td></tr>
        <tr><th>Teléfono</th><td class="popover-texto">{{ $cli?->telefono ?: '—' }}</td></tr>
        <tr><th>Cobrar</th><td>{{ trim((string) $cli?->auxcobro?->accion) ?: '—' }}</td></tr>
        <tr><th>Cobranza</th><td>{{ trim((string) $cli?->auxpago?->nombre) ?: '—' }}</td></tr>
        <tr><th>Modo de Pago</th><td>{{ trim((string) $cli?->auxtcobro?->nombre) ?: '—' }}</td></tr>
      </table>
    </div>
    <div id="entrega-pedido-{{ $distribucion->id }}">
      <table class="table table-sm table-bordered mb-0 text-xs">
        <tr><th>Horario Entrega</th><td>Mañana: {{ horario($cli?->desde, $cli?->hasta) }}<br>Tarde: {{ horario($cli?->desde1, $cli?->hasta1) }}</td></tr>
        <tr><th>Lun Cerrado</th><td>{{ $cli?->lunes ?: '—' }}</td></tr>
        <tr><th>Sáb Recibe</th><td>{{ $cli?->sabado ?: '—' }}</td></tr>
        <tr><th>Obs. Recepción</th><td class="popover-texto">{{ $cli?->obsrecep ?: '—' }}</td></tr>
        <tr><th>Comentarios</th><td class="popover-texto">{{ $distribucion->observaciones ?: '—' }}</td></tr>
      </table>
    </div>
    @endforeach
  </div>

  <!-- Tabla de pedidos del día: en la computadora una fila por pedido, en el celular una tarjeta -->
  <div class="reparto-scroll">
  <table class="table table-sm table-bordered table-hover table-sticky w-100 reparto-tabla">
    <thead>
      <tr>
        <th>Pedido</th>
        <th class="text-center" title="P: productos · T: tareas · PT: ambos">Tipo</th>
        <th class="text-left">Nombre Fantasía</th>
        <th class="text-left">Razón Social</th>
        <th class="text-center">Chofer</th>
        <th class="text-center">Orden</th>
        <th class="text-center">Fecha Fac.</th>
        <th class="text-center">Nro. Fac.</th>
        <th class="text-center">Importe Fac.</th>
        <th class="text-center c-angosta" title="Lleva factura impresa"><i class="fas fa-print"></i></th>
        <th class="c-angosta" colspan="3"></th>
      </tr>
    </thead>
    <tbody>
      @foreach ($distribuciones as $distribucion)
      @php
        $abrir = route('distribucion_reparto.show', ['distribucion_reparto' => $distribucion->id, 'volver' => $fecha]);
        $form = 'distribucion-form-' . $distribucion->id;
      @endphp
      <tr>
        <td class="c-pedido"><span class="d-md-none">N° </span>{{ $distribucion->id }}</td>
        <td class="c-tipo text-center">{{ $distribucion->tipo }}</td>
        {{-- Al pasar el mouse muestra la ficha del cliente; al hacer clic abre el pedido --}}
        <td class="c-fantasia">
          <a href="{{ $abrir }}" class="reparto-cliente" data-pedido="{{ $distribucion->id }}">{{ $distribucion->distribucion->nomfantasia ?: $distribucion->distribucion->razonsocial }}</a>
        </td>
        <td class="c-razon"><span class="reparto-cliente" data-pedido="{{ $distribucion->id }}">{{ $distribucion->distribucion->razonsocial }}</span></td>
        <td class="text-center c-chofer" data-label="Chofer">
          <input type="text" name="chofer" form="{{ $form }}" value="{{ $distribucion->chofer }}" class="form-control form-control-sm text-center reparto-campo" maxlength="2" />
        </td>
        <td class="text-center c-orden" data-label="Orden">
          <input type="number" name="orden" form="{{ $form }}" value="{{ $distribucion->orden }}" class="form-control form-control-sm text-center reparto-campo reparto-entrega" data-pedido="{{ $distribucion->id }}" />
        </td>
        @if ($distribucion->tipo != 'T')
        <td class="text-center c-ffac" data-label="Fecha factura">
          <input type="date" name="fechaFactura" form="{{ $form }}" value="{{ $distribucion->fechaFactura }}"
            class="form-control form-control-sm text-center reparto-campo factura-fecha" data-label="Fecha Factura" />
        </td>
        <td class="text-center c-nfac" data-label="Nro. factura">
          <input type="text" name="nroFactura" form="{{ $form }}" value="{{ $distribucion->nroFactura }}"
            class="form-control form-control-sm text-center reparto-campo factura-nro" data-label="Nro. Factura" maxlength="20" />
        </td>
        <td class="text-center c-ifac" data-label="Importe factura (pedido {{ pesos($distribucion->totalPedido) }})">
          {{-- En rojo cuando el importe de la factura no coincide con el total del pedido; sin importe cargado no se marca --}}
          <input type="number" name="totalFactura" form="{{ $form }}" value="{{ $distribucion->totalFactura }}"
            data-total-pedido="{{ $distribucion->totalPedido }}" data-pedido="{{ $distribucion->id }}"
            title="Diferencia: $ {{ numero($distribucion->totalFactura - $distribucion->totalPedido, 2) }}"
            class="form-control form-control-sm text-right reparto-campo factura-importe reparto-productos {{ (float) $distribucion->totalFactura != 0 && abs($distribucion->totalFactura - $distribucion->totalPedido) > 0.01 ? 'factura-difiere' : '' }}"
            data-label="Importe Factura" step="0.01" />
        </td>
        {{-- Solo se marca el cliente que lleva la factura impresa; si no, la celda queda vacía --}}
        <td class="text-center c-imp {{ \App\Models\Distribucion::esSi($distribucion->distribucion->fac_imp) ? 'reparto-imprimir' : '' }}">
          @if (\App\Models\Distribucion::esSi($distribucion->distribucion->fac_imp))
          <i class="fas fa-print text-success" title="Imprimir factura"></i><span class="d-md-none small ml-1 text-success">Imprimir factura</span>
          @endif
        </td>
        <td class="c-recibo text-center">
          <a href="{{ route('distribucion_reparto.imprimirRecibo', ['id' => $distribucion->id]) }}"
            class="btn btn-sm btn-outline-info" title="Imprimir recibo" target="_blank">
            <i class="fas fa-receipt"></i><span class="d-md-none ml-1">Recibo</span>
          </a>
        </td>
        @else
        {{-- Solo tareas: sin factura ni recibo --}}
        <td class="c-vacio"></td>
        <td class="c-vacio"></td>
        <td class="c-vacio"></td>
        <td class="c-vacio"></td>
        <td class="c-vacio"></td>
        @endif
        {{-- Un solo botón para ver el pedido completo; desde ahí se edita y se vuelve a este día --}}
        <td class="c-abrir text-center">
          <a href="{{ $abrir }}" class="btn btn-sm btn-outline-primary" title="Ver / editar pedido">
            <i class="fas fa-pen-to-square"></i><span class="d-md-none ml-1">Ver / editar</span>
          </a>
        </td>
        {{-- Factura, chofer y orden se guardan solos al salir del campo; acá se ve si se guardó --}}
        <td class="c-guardar text-center"><span class="reparto-estado" id="estado-pedido-{{ $distribucion->id }}" title="Se guarda solo al salir del campo"></span></td>
      </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr class="font-weight-bold">
        <td colspan="8" class="text-right">{{ $distribuciones->count() }} pedidos · Total pedidos {{ pesos($distribuciones->sum('totalPedido')) }}</td>
        <td class="text-right"><span class="d-md-none">Facturado </span>{{ pesos($distribuciones->sum('totalFactura')) }}</td>
        <td colspan="4" class="text-muted font-weight-normal">Facturado del día. Los pedidos sin chofer van primero.</td>
      </tr>
    </tfoot>
  </table>
  </div>

</div>
</div>

<!-- Scripts -->
<script>
  // Botones y Campo de Fecha
  const hoyBtn = document.getElementById('hoy-btn');
  const anteriorBtn = document.getElementById('anterior-btn');
  const posteriorBtn = document.getElementById('posterior-btn');
  const fechaInput = document.getElementById('fecha-input');
  const fechaForm = document.getElementById('fecha-form');

  // Fechas en hora local (toISOString usa UTC y después de las 21 h saltaba al día siguiente)
  function ymd(d) {
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
  }
  function parseYmd(s) {
    const [a, m, d] = (s || '').split('-').map(Number);
    return a ? new Date(a, m - 1, d) : new Date();
  }

  function irA(fecha) {
    fechaInput.value = ymd(fecha);
    fechaForm.submit();
  }

  // Calendario: los días con pedidos se muestran en negrita (con un punto verde)
  const diasConPedidos = {};
  const mesesCargados = new Set();

  async function cargarMes(fp) {
    const clave = `${fp.currentYear}-${fp.currentMonth}`;
    if (mesesCargados.has(clave)) return;
    mesesCargados.add(clave);
    // Incluye los días de los meses vecinos que se ven en la grilla
    const desde = ymd(new Date(fp.currentYear, fp.currentMonth, -6));
    const hasta = ymd(new Date(fp.currentYear, fp.currentMonth + 1, 7));
    try {
      const url = `{{ route('distribucion_reparto.fechas') }}?desde=${desde}&hasta=${hasta}`;
      const r = await fetch(url, { headers: { 'Accept': 'application/json' } });
      // Sesión vencida (pasa en el celular con la pestaña abierta de días): recargar lleva al login.
      // Una sola vez, para no quedar recargando si el problema es otro.
      if (r.status === 401 || r.status === 419) {
        try {
          if (!sessionStorage.getItem('recarga-sesion')) { sessionStorage.setItem('recarga-sesion', '1'); return window.location.reload(); }
        } catch (e) {}
        throw new Error(r.status);
      }
      try { sessionStorage.removeItem('recarga-sesion'); } catch (e) {}
      if (!r.ok) throw new Error(r.status);
      Object.assign(diasConPedidos, await r.json());
      fp.redraw();
    } catch (e) {
      mesesCargados.delete(clave);
      console.error(e);
    }
  }

  const calendario = flatpickr(fechaInput, {
    locale: 'es',
    dateFormat: 'Y-m-d',
    altInput: true,
    altFormat: 'd/m/Y',
    altInputClass: fechaInput.className,
    allowInput: true,
    disableMobile: true,  // en el celular también: el calendario nativo no marca los días con pedidos
    onReady: (sel, str, fp) => cargarMes(fp),
    onMonthChange: (sel, str, fp) => cargarMes(fp),
    onYearChange: (sel, str, fp) => cargarMes(fp),
    onOpen: (sel, str, fp) => cargarMes(fp),
    onDayCreate: (sel, str, fp, dia) => {
      const cantidad = diasConPedidos[ymd(dia.dateObj)];
      if (cantidad) {
        dia.classList.add('con-pedidos');
        dia.title = `${cantidad} pedido${cantidad > 1 ? 's' : ''}`;
      }
    },
    onChange: (sel, str) => { if (str) fechaForm.submit(); },
  });

  // Eventos de los Botones
  hoyBtn.addEventListener('click', () => irA(new Date()));

  anteriorBtn.addEventListener('click', () => {
    const f = parseYmd(fechaInput.value);
    f.setDate(f.getDate() - 1);
    irA(f);
  });

  posteriorBtn.addEventListener('click', () => {
    const f = parseYmd(fechaInput.value);
    f.setDate(f.getDate() + 1);
    irA(f);
  });

function verificarDatos(event, url) {
  event.preventDefault(); // Evita que el enlace se abra directamente

  let faltanDatos = false;
  let mensaje = "Faltan los siguientes datos:\n\n";

  document.querySelectorAll(".factura-fecha, .factura-nro, .factura-importe").forEach(input => {
    if (input.value.trim() === "") {
      faltanDatos = true;
      mensaje += `- ${input.getAttribute("data-label")}\n`;
    }
  });

  if (faltanDatos) {
    mensaje += "\n"; // Agregar una línea en blanco después de la lista de datos faltantes

    Swal.fire({
      title: "¡Atención!",
      text: mensaje,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Continuar de todos modos",
      cancelButtonText: "Cancelar"
    }).then((result) => {
      if (result.isConfirmed) {
        window.open(url, "_blank"); // Si el usuario confirma, abrir la impresión
      }
    });
  } else {
    window.open(url, "_blank"); // Si todo está completo, abrir directamente
  }
}



  // Importe de factura: se pone en rojo mientras se escribe si no coincide con el total del pedido (vacío no se marca)
  function numeroAr(n) {
    return n.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
  document.querySelectorAll('.factura-importe').forEach(input => {
    input.addEventListener('input', () => {
      const importe = parseFloat(input.value) || 0;
      const dif = importe - (parseFloat(input.dataset.totalPedido) || 0);
      input.classList.toggle('factura-difiere', importe !== 0 && Math.abs(dif) > 0.01);
      input.title = `Diferencia: $ ${numeroAr(dif)}`;
    });
  });

  // Guardado automático: al salir de un campo (o con Enter) se guarda la fila sin recargar la página.
  // Si se cambia otro campo de la misma fila mientras se guarda, se vuelve a guardar al terminar.
  const guardandoFila = {}, pendienteFila = {};
  function marcarEstado(id, clase, titulo) {
    const span = document.getElementById('estado-pedido-' + id);
    if (!span) return;
    span.innerHTML = clase ? `<i class="fas ${clase} fs-xs"></i>` : '';
    span.title = titulo;
  }
  async function guardarFila(form) {
    const id = form.id.replace('distribucion-form-', '');
    if (guardandoFila[id]) { pendienteFila[id] = true; return; }
    guardandoFila[id] = true;
    marcarEstado(id, 'fa-spinner fa-spin text-muted', 'Guardando...');
    try {
      const r = await fetch(form.action, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form), // incluye los campos de la fila (form="...") y el _method PUT
      });
      if (r.ok) {
        marcarEstado(id, 'fa-check text-success', 'Guardado a las ' + new Date().toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' }));
      } else {
        const datos = await r.json().catch(() => ({}));
        const errores = datos.errors ? Object.values(datos.errors).flat().join('\n') : 'Error ' + r.status;
        marcarEstado(id, 'fa-exclamation-triangle text-danger', 'No se guardó: ' + errores);
        Swal.fire({ title: 'No se guardó el pedido ' + id, text: errores, icon: 'error' });
      }
    } catch (e) {
      marcarEstado(id, 'fa-exclamation-triangle text-danger', 'No se guardó: sin conexión');
      Swal.fire({ title: 'No se guardó el pedido ' + id, text: 'Revisá la conexión y volvé a cambiar el dato.', icon: 'error' });
    }
    guardandoFila[id] = false;
    if (pendienteFila[id]) { pendienteFila[id] = false; guardarFila(form); }
  }
  document.querySelectorAll('.reparto-tabla input[form^="distribucion-form-"]').forEach(input => {
    input.addEventListener('change', () => guardarFila(input.form));
    input.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); input.blur(); } });
  });
  // Avisar si se quiere salir de la página mientras algo se está guardando
  window.addEventListener('beforeunload', e => {
    if (Object.values(guardandoFila).some(Boolean)) { e.preventDefault(); e.returnValue = ''; }
  });

  // Carteles al pasar el mouse: productos del pedido (importe), datos de entrega (orden) y ficha del cliente (nombres)
  // jQuery y Bootstrap se cargan al final de la página: esperar a que estén
  document.addEventListener('DOMContentLoaded', function () {
    $('.reparto-productos, .reparto-entrega, .reparto-cliente').each(function () {
      const tipo = ['entrega', 'cliente'].find(t => this.classList.contains('reparto-' + t)) || 'productos';
      const plantilla = document.getElementById(tipo + '-pedido-' + this.dataset.pedido);
      if (!plantilla) return;
      $(this).popover({
        trigger: 'hover',
        placement: window.innerWidth < 768 ? 'bottom' : 'right',
        html: true,
        sanitize: false, // el contenido viene de la plantilla del servidor, ya escapado
        container: 'body',
        boundary: 'window',
        title: 'Pedido ' + this.dataset.pedido,
        content: plantilla.innerHTML,
        template: '<div class="popover popover-productos" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body p-1"></div></div>',
      });
    });
  });

</script>
@endsection