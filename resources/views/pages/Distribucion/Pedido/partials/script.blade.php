<script>
(function () {
  const alta = @json($modo === 'alta');
  const selCliente = document.getElementById('cliente_id');
  const cuerpoLista = document.querySelector('#productosCliente tbody');
  const detalle = document.querySelector('#detalleTable tbody');
  const $ = id => document.getElementById(id);
  let productos = [];
  let consulta = 0;

  const clienteId = () => alta ? selCliente.value : @json((string) $cliente);
  const esc = t => String(t ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  // Misma regla que productoCDA::porcentajeIva() y calcularTotalLinea() en el servidor
  const ivaDe = iva => parseFloat(String(iva ?? '').replace(',', '.')) === 10.5 ? 10.5 : 21;
  const pesos = n => '$ ' + (Number(n) || 0).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const totalLinea = (precio, cantidad, iva) => Math.round((parseFloat(precio) || 0) * (parseFloat(cantidad) || 0) * (1 + iva / 100) * 100) / 100;

  function aviso(texto, clase) {
    cuerpoLista.innerHTML = `<tr><td colspan="9" class="text-center ${clase || 'text-muted'}">${texto}</td></tr>`;
  }

  async function cargarLista() {
    const id = clienteId();
    const esta = ++consulta;   // si se cambia de cliente antes de que responda, se descarta
    productos = [];
    if (!id) { aviso('Seleccione un cliente.'); return; }
    aviso('Cargando…');
    try {
      const r = await fetch(`/distribucion/${id}/productos-tareas`, { headers: { Accept: 'application/json' } });
      if (!r.ok) throw new Error(r.status);
      const datos = await r.json();
      if (esta !== consulta) return;
      productos = datos.productos;
      mostrarLista();
      $('tarea_id').innerHTML = '<option value="">Seleccione</option>'
        + datos.tareas.map(t => `<option value="${t.id}">${esc(t.tarea)}</option>`).join('');
    } catch (e) {
      if (esta === consulta) aviso('No se pudo cargar la lista de productos. Vuelva a intentar.', 'text-danger');
    }
  }

  function mostrarLista() {
    if (!productos.length) { aviso('El cliente todavía no tiene productos en su lista.'); return; }
    cuerpoLista.innerHTML = productos.map((p, i) => `
      <tr data-i="${i}" data-producto="${p.producto_id}">
        <td>${esc(p.nombre)}</td>
        <td class="text-right">${pesos(p.precio)}</td>
        <td class="text-center">${esc(p.fecha)}</td>
        <td class="text-center">${esc(p.fechaUEnt)}</td>
        <td><input type="number" min="1" step="1" class="form-control form-control-sm lista-cantidad" aria-label="Cantidad"></td>
        <td class="text-center"><input type="checkbox" class="lista-flag" aria-label="Flag"></td>
        <td class="text-center"><input type="checkbox" class="lista-sc" aria-label="Sin cargo"></td>
        <td class="text-center"><input type="checkbox" class="lista-retirar" aria-label="Retirar"></td>
        <td class="text-center"><button type="button" class="btn btn-success btn-sm lista-agregar">Agregar</button></td>
      </tr>`).join('');
  }

  function agregarProducto(fila) {
    const p = productos[fila.dataset.i];
    const campo = fila.querySelector('.lista-cantidad');
    const cantidad = Number(campo.value);
    if (!Number.isInteger(cantidad) || cantidad < 1) { campo.focus(); alert('Ingrese la cantidad (número entero).'); return; }
    const n = alta
      ? { id: 'productos', precio: 'precios', cantidad: 'cantidades', flag: 'flag', sc: 'sin_cargo', retirar: 'retirar' }
      : { id: 'new_productos', precio: 'new_precios', cantidad: 'new_cantidades', flag: 'new_flag', sc: 'new_sin_cargo', retirar: 'new_retirar' };
    const marca = (nombre, si) => `<td class="text-center"><input type="hidden" name="${nombre}[]" value="${si ? 1 : 0}"><input type="checkbox" data-marca ${si ? 'checked' : ''}></td>`;
    const tr = document.createElement('tr');
    tr.className = 'producto-row';
    tr.dataset.iva = ivaDe(p.ivacda);
    tr.innerHTML = `
      <td>Producto</td>
      <td>${esc(p.nombre)}
        <input type="hidden" name="${n.id}[]" value="${p.producto_id}">
        ${alta ? '<input type="hidden" name="tipos[]" value="P"><input type="hidden" name="detalles[]" value="">' : ''}
      </td>
      <td><input type="number" step="0.01" min="0" name="${n.precio}[]" value="${p.precio ?? ''}" class="form-control" required></td>
      <td><input type="number" min="0" name="${n.cantidad}[]" value="${cantidad}" class="form-control" required></td>
      ${marca(n.flag, fila.querySelector('.lista-flag').checked)}
      ${marca(n.sc, fila.querySelector('.lista-sc').checked)}
      ${marca(n.retirar, fila.querySelector('.lista-retirar').checked)}
      <td class="linea-total"></td>
      <td><button type="button" class="btn btn-danger remove-row">X</button></td>`;
    detalle.appendChild(tr);
    campo.value = '';
    fila.querySelectorAll('input[type=checkbox]').forEach(i => { i.checked = false; });
    actualizarTotales();
  }

  function agregarTarea() {
    const sel = $('tarea_id');
    if (!sel.value) { alert('Seleccione una tarea.'); return; }
    const tr = document.createElement('tr');
    tr.className = 'tarea-row';
    tr.innerHTML = `
      <td>Tarea</td>
      <td>${esc(sel.options[sel.selectedIndex].text)}
        <input type="text" name="${alta ? 'detalles' : 'new_tarea_detalles'}[]" value="${esc($('tarea_detalle').value)}" class="form-control form-control-sm" placeholder="Detalle">
        ${alta
          ? `<input type="hidden" name="productos[]" value="${sel.value}"><input type="hidden" name="tipos[]" value="T">
             <input type="hidden" name="precios[]" value=""><input type="hidden" name="cantidades[]" value="">
             <input type="hidden" name="flag[]" value="0"><input type="hidden" name="sin_cargo[]" value="0"><input type="hidden" name="retirar[]" value="0">`
          : `<input type="hidden" name="new_tareas[]" value="${sel.value}">`}
      </td>
      <td></td><td></td><td></td><td></td><td></td><td></td>
      <td><button type="button" class="btn btn-danger remove-row">X</button></td>`;
    detalle.appendChild(tr);
    sel.value = $('tarea_detalle').value = '';
  }

  function actualizarTotales() {
    let total = 0;
    detalle.querySelectorAll('.producto-row').forEach(function (tr) {
      const precio = tr.querySelector('input[name$="precios[]"]').value;
      const cantidad = tr.querySelector('input[name$="cantidades[]"]').value;
      const t = totalLinea(precio, cantidad, parseFloat(tr.dataset.iva) || 21);
      tr.querySelector('.linea-total').textContent = pesos(t);
      total += t;
    });
    $('total-pedido').textContent = pesos(total);
  }

  function mostrarCrear() {
    const crear = $('catalogo_crear').checked;
    $('catalogo_existente').classList.toggle('d-none', crear);
    document.querySelectorAll('[data-catalogo-nuevo]').forEach(c => c.classList.toggle('d-none', !crear));
  }

  // Alta del producto en la lista del cliente, sin salir del pedido
  async function agregarAlCliente() {
    const error = $('catalogo_error'), sel = $('catalogo_id'), boton = $('catalogo_agregar');
    if (boton.disabled) return;
    error.textContent = '';
    if (!clienteId()) { error.textContent = 'Primero seleccione el cliente.'; return; }
    const crear = $('catalogo_crear').checked;
    if (crear && (!$('catalogo_nombre').value.trim() || !$('catalogo_iva').value)) { error.textContent = 'Ingrese el nombre y el IVA del producto nuevo.'; return; }
    if ((!crear && !sel.value) || $('catalogo_precio').value === '') { error.textContent = 'Seleccione el producto e ingrese el precio.'; return; }
    boton.disabled = true;
    try {
      const r = await fetch(@json(route('distribucion_producto.store', [], false)), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
        body: JSON.stringify({
          distribucion_id: clienteId(),
          ...(crear ? { producto_nuevo: $('catalogo_nombre').value.trim(), ivacda: $('catalogo_iva').value } : { producto_id: sel.value }),
          precio: $('catalogo_precio').value, fecha: $('catalogo_fecha').value,
        }),
      });
      const d = await r.json().catch(() => ({}));
      if (!r.ok) {
        error.textContent = d.errors ? Object.values(d.errors).flat().join(' ') : (d.message || 'No se pudo agregar el producto.');
        return;
      }
      if (sel.tomselect) sel.tomselect.clear(); else sel.value = '';
      $('catalogo_precio').value = $('catalogo_nombre').value = $('catalogo_iva').value = '';
      $('catalogo_crear').checked = false;
      mostrarCrear();
      await cargarLista();
      cuerpoLista.querySelector(`tr[data-producto="${d.producto_id}"] .lista-cantidad`)?.focus();
    } catch (e) {
      error.textContent = 'No se pudo agregar el producto: revise la conexión.';
    } finally {
      boton.disabled = false;
    }
  }

  cuerpoLista.addEventListener('click', e => {
    const b = e.target.closest('.lista-agregar');
    if (b) agregarProducto(b.closest('tr'));
  });
  // Enter en la cantidad agrega el producto (no envía el pedido)
  cuerpoLista.addEventListener('keydown', e => {
    if (e.key === 'Enter' && e.target.matches('input')) { e.preventDefault(); agregarProducto(e.target.closest('tr')); }
  });
  $('tarea_agregar').addEventListener('click', agregarTarea);
  $('catalogo_agregar')?.addEventListener('click', agregarAlCliente);
  $('catalogo_crear')?.addEventListener('change', mostrarCrear);
  $('tarea_detalle').addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); agregarTarea(); }
  });
  ['catalogo_nombre', 'catalogo_precio', 'catalogo_fecha'].forEach(id => $(id)?.addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); agregarAlCliente(); }
  }));
  detalle.addEventListener('click', e => {
    if (e.target.closest('.remove-row')) { e.target.closest('tr').remove(); actualizarTotales(); }
  });
  detalle.addEventListener('input', actualizarTotales);
  detalle.addEventListener('change', e => {
    if (e.target.matches('[data-marca]')) e.target.previousElementSibling.value = e.target.checked ? 1 : 0;
  });

  if (alta) {
    selCliente.addEventListener('change', function () {
      detalle.innerHTML = '';   // los productos y precios son de cada cliente
      actualizarTotales();
      cargarLista();
    });
    $('pedido-form').addEventListener('submit', function (e) {
      if (!detalle.rows.length) { e.preventDefault(); alert('Agregue al menos un producto o una tarea.'); }
    });
  }
  actualizarTotales();
  cargarLista();
})();
</script>
