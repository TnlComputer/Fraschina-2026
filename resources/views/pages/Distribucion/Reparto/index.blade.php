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
<div class="container">
  <h1>Distribución Reparto</h1>

  <!-- Botones para filtrar por fechas -->
  <div class="mb-3 d-flex justify-content-between align-items-center">
    <!-- Botón Nuevo Pedido a la Izquierda -->
    <div>
      </a><a href="{{ route('distribucion_pedido.create') }}" class="btn btn-primary ml-3">
        <i class="fas fa-plus"></i> Nuevo Pedido
      </a> <!-- Formulario de búsqueda -->
    </div>
    <!-- Botón Reparto Impresion -->
    <div>
      <a href="{{ route('distribucion_reparto.imprimirReparto', ['fecha' => $fecha]) }}" title="Impresión Reparto"
        target="_blank" class="btn btn-info"><i class="fas fa-print"></i>
        Reparto
      </a>
    </div> <!-- Botón Control Impresion -->
    {{-- <div>
      <a href="{{ route('distribucion_reparto.imprimirControl', ['fecha' => $fecha]) }}" title="Impresi´on Control"
        target="_blank" class="btn btn-secondary"><i class="fas fa-print"></i> Control
      </a>
    </div> --}}
    <div>
      <a href="#"
        onclick="verificarDatos(event, '{{ route('distribucion_reparto.imprimirControl', ['fecha' => $fecha]) }}')"
        title="Impresión Control" class="btn btn-secondary">
        <i class="fas fa-print"></i> Control
      </a>
    </div>
    <!-- Formulario y Botones de Cambio de Fecha a la derecha -->
    <form method="GET" action="{{ route('distribucion_reparto.index') }}" id="fecha-form"
      class="d-flex align-items-center">
      <!-- Campo de Fecha -->
      <input type="text" name="fecha" id="fecha-input" value="{{ $fecha }}" autocomplete="off"
        class="form-control d-inline-block w-auto h-100" style="height: calc(2.25rem + 2px); background: #fff;"
        title="Los días en negrita tienen pedidos">

      <!-- Botones de Cambio de Fecha -->
      <button type="submit" class="btn btn-success mx-1 h-100">Buscar por Fecha</button>
      <button type="button" class="btn btn-secondary mx-1 h-100" id="anterior-btn">Día Anterior</button>
      <button type="button" class="btn btn-info mx-1 h-100" id="posterior-btn">Día Posterior</button>
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

  <!-- Tabla de distribuciones -->
  <table class="table table-sm text-xs table-bordered w-100">
    <thead>
      <tr>
        <th class="text-center">Reparto</th>
        <th colspan="4"></th>
        <th class="text-center">Fecha Fac</th>
        <th class="text-center">Nro.Fac</th>
        <th class="text-center">Imp.Fac</th>
        <th><i class="fas fa-print"></i></th>
        <th class="text-center">Chofer</th>
        <th class="text-center">Orden</th>
        <th>Pedido</th>
        <th>PT</th>
        <th>Nombre Fantasia</th>
        <th>Razón Social</th>
      </tr>
    </thead>
    <tbody class="text-xs bold">
      @foreach ($distribuciones as $distribucion)
      {{-- @if ($distribucion->tipo == 'P' || $distribucion->tipo == 'PT') --}}
<tr>
          <td class=" text-center">{{ $distribucion->fechaEntrega }}</td>
          @if ($distribucion->tipo != 'T')
          <td>
            <a href="{{ route('distribucion_reparto.imprimirRecibo', ['id' => $distribucion->id]) }}"
              class="btn btn-xs mx-1" title="Impresi´on Recibo" target="_blank">
              <i class="fas fa-print fs-xs text-info"></i>
            </a>
          </td>
          <td>
            <a href="{{ route('distribucion_reparto.show', ['distribucion_reparto' => $distribucion->id]) }}"
              class="btn btn-xs" title="Ver Pedido">
              <i class="fas fa-eye fs-xs text-success"></i>
            </a>
          </td>

          <td>
            <a href="{{ route('distribucion_pedido.edit', ['distribucion_pedido' => $distribucion->id]) }}"
              class="btn btn-xs" title="Editar Pedido">
              <i class="fas fa-pen fs-xs text-blue"></i>
            </a>
          </td>
          @else
          <td></td>
          <td></td>
          <td>
            <a href="{{ route('distribucion_pedido.edit', ['distribucion_pedido' => $distribucion->id]) }}"
              class="btn btn-xs" title="Editar Pedido">
              <i class="fas fa-pen fs-xs text-blue"></i>
            </a>
            {{-- <a
              href="{{ route('distribucion_pedido.edit', ['distribucion_pedido' => $distribucion->id, 'fecha' => $fecha]) }}"
              class="btn btn-xs" title="Editar Pedido">
              <i class="fas fa-pen fs-xs text-blue"></i>
            </a> --}}
          </td>
          @endif
          <td><button type="submit" form="distribucion-form-{{ $distribucion->id }}" class="btn btn-xs" title="Guardar factura, chofer y orden">
              <i class="fas fa-sync-alt fs-xs text-orange"></i>
            </button>
          </td>
          @if ($distribucion->tipo != 'T')
          <td class="text-center">
            <input type="date" name="fechaFactura" form="distribucion-form-{{ $distribucion->id }}" value="{{ $distribucion->fechaFactura }}"
              class="text-center text-xs factura-fecha" data-label="Fecha Factura" style="width: 100px;" />
          </td>
          <td class="text-center">
            <input type="text" name="nroFactura" form="distribucion-form-{{ $distribucion->id }}" value="{{ $distribucion->nroFactura }}"
              class=" text-center text-xs factura-nro" data-label="Nro. Factura" style="width: 60px;" />
          </td>
          <td class="text-center">
            @php
            $dif = abs($distribucion->totalFactura - $distribucion->totalPedido);
            @endphp

            @if ($dif > 0.01)
            <input type="number" name="totalFactura" form="distribucion-form-{{ $distribucion->id }}" value="{{ $distribucion->totalFactura }}"
              title="$ {{ number_format($distribucion->totalFactura - $distribucion->totalPedido, 2) }}"
              class="text-center text-xs bg-red factura-importe" data-label="Importe Factura" step="0.01"
              style="width: 90px;" />
            @else
            <input type="number" name="totalFactura" form="distribucion-form-{{ $distribucion->id }}" value="{{ $distribucion->totalFactura }}"
              title="$ {{ number_format($distribucion->totalFactura - $distribucion->totalPedido, 2) }}"
              class="text-center text-xs factura-importe" data-label="Importe de Factura" step="0.01"
              style="width: 90px;" />
            @endif
          </td>
          <td class="{{ \App\Models\Distribucion::esSi($distribucion->distribucion->fac_imp) ? 'bg-yellow-300' : '' }}">
            @if (\App\Models\Distribucion::esSi($distribucion->distribucion->fac_imp))
            <i class="fas fa-print text-success" title="Factura Impresa"></i>
            @else
            <i class="fas fa-times-circle text-danger" title="Factura No Impresa"></i>
            @endif
          </td>
          @else
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          @endif
          <td class="text-center">
            <input type="text" name="chofer" form="distribucion-form-{{ $distribucion->id }}" value="{{ $distribucion->chofer }}" class="text-center text-xs"
              style="width: 30px;" />
          </td>
          <td class="text-center">
            <input type="number" name="orden" form="distribucion-form-{{ $distribucion->id }}" value="{{ $distribucion->orden }}" class="text-center text-xs"
              style="width: 40px;" />
          </td>
          {{-- @else
          <td colspan="11"></td>
          @endif --}}
          <td>{{ $distribucion->id }}</td>
          <td>{{ $distribucion->tipo }}</td>
          <!-- En la vista donde quieres mostrar el modal -->
          <td>
            <button type="button" class="btn btn-xs" data-toggle="modal"
              data-target="#infoModal_{{ $distribucion->id }}">
              {{ $distribucion->distribucion->nomfantasia }}
            </button>
          </td>

          <!-- Modal para mostrar la información del pedido -->
          <div class="modal fade" id="infoModal_{{ $distribucion->id }}" tabindex="-1" role="dialog"
            aria-labelledby="infoModalLabel_{{ $distribucion->id }}" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <!-- Encabezado del Modal -->
                <div class="modal-header">
                  <h5 class="modal-title" id="infoModalLabel_{{ $distribucion->id }}">Información del Cliente</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <!-- Cuerpo del Modal -->
                <div class="modal-body">
                  <strong>Razón Social:</strong> {{ $distribucion->distribucion->nomfantasia }}<br>
                  <strong>Dirección:</strong> {{ $distribucion->distribucion->auxCalles?->calle }} {{
                  $distribucion->distribucion->dire_nro }}
                  {{ $distribucion->distribucion->piso }}
                  {{ $distribucion->distribucion->dpto }} <br>
                  {{ $distribucion->distribucion->auxbarrio?->nombrebarrio }} -
                  {{ $distribucion->distribucion->auxlocalidad?->localidad }} - {{
                  $distribucion->distribucion->auxmunicipio?->ciudadmunicipio }} <br>
                  <strong>Horarios:</strong><br> Mañana: {{ $distribucion->distribucion->desde }} - {{
                  $distribucion->distribucion->hasta }}<br>
                  Tarde: {{ $distribucion->distribucion->desde1 }} -{{ $distribucion->distribucion->hasta1 }}<br>
                  <strong>Observaciones:</strong><br>
                  {{$distribucion->distribucion->obsrecep }}<br>
                </div>
                <!-- Pie del Modal -->
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
              </div>
            </div>
          </div>

          <td>{{ $distribucion->distribucion->razonsocial }}</td>
        </tr>
        @endforeach

      
    </tbody>
  </table>

</div>
<!-- Paginación -->
<div class="d-flex justify-content-center">

</div>

</div>

<!-- Modal -->
<div class="modal fade" id="modalInfo" tabindex="-1" aria-labelledby="modalInfoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalInfoLabel">Información del Pedido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="modal-content-body"></div> <!-- Aquí se insertará el contenido dinámico -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
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
      Object.assign(diasConPedidos, await (await fetch(url, { headers: { 'Accept': 'application/json' } })).json());
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



  // Evitar que el formulario se envíe al presionar Enter accidentalmente
  document.querySelector('form').addEventListener('keydown', function(event) {
  if (event.key === 'Enter') {
  event.preventDefault(); // Evita el envío del formulario
  }
  });

  $(document).ready(function(){
    // Al hacer clic en el nombre de fantasía, abrir el modal y cargar contenido
    $('#openModal').on('click', function(e) {
      e.preventDefault();

      var id = $(this).data('id');
      var calle = $(this).data('calle');
      var direccion = $(this).data('direccion');
      var piso = $(this).data('piso');
      var dpto = $(this).data('dpto');
      var barrio = $(this).data('barrio');
      var localidad = $(this).data('localidad');
      var desde = $(this).data('desde');
      var hasta = $(this).data('hasta');
      var desde1 = $(this).data('desde1');
      var hasta1 = $(this).data('hasta1');
      var nomfantasia = $(this).data('nomfantasia');

      // Crear el contenido del modal
      var modalContent = `
        <p><strong>Dirección:</strong> ${calle} ${direccion} ${piso} ${dpto} ${barrio} - ${localidad}</p>
        <p><strong>Horarios:</strong> Mañana: ${desde} - ${hasta} | Tarde: ${desde1} - ${hasta1}</p>
        <p><strong>Nombre de Fantasía:</strong> ${nomfantasia}</p>
      `;

      // Insertar el contenido en el cuerpo del modal
      $('#modal-content-body').html(modalContent);

      // Mostrar el modal
      $('#modalInfo').modal('show');
    });
  });
</script>
@endsection