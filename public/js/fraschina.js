/*
 * Ajustes comunes a todas las pantallas (cargado por AdminLTE, ver config/adminlte.php → plugins → Fraschina).
 *
 * Tablas en el celular:
 *  - Los listados (tablas con encabezado de 4 columnas o más) se ven como tarjetas: cada fila es una
 *    tarjeta y cada dato lleva el nombre de su columna (data-label, que usa fraschina.css).
 *  - El resto de las tablas queda dentro de un contenedor con desplazamiento lateral, para que no
 *    ensanchen la página (si no, la barra superior se estira y el contenido se corta).
 * Una tabla puede excluirse de las tarjetas con la clase .sin-tarjetas.
 *
 * Selectores con buscador: un <select data-buscador> se puede escribir para filtrar la lista
 * (por nombre, razón social o número). Usa Tom Select, que se carga solo si la página lo necesita;
 * el <select> original sigue siendo el que se envía y dispara 'change' como siempre.
 *
 * Buscar mientras se escribe: en los listados (form.form__buscar o form[data-buscar-al-escribir])
 * el texto del buscador se envía solo al dejar de tipear, y los desplegables, fechas y casillas de
 * los filtros al cambiarlos; se pide la misma página al servidor y se reemplaza únicamente el listado
 * (el filtro queda como está, con el cursor). La dirección se actualiza para que recargar, paginar o
 * volver atrás conserven la búsqueda. Si la página no tiene listado todavía, el cambio envía el filtro.
 */
(function () {
  'use strict';

  // Tablas que ya tienen su propia vista para el celular
  const PROPIAS = '.reparto-tabla, .agenda-tabla, .sin-tarjetas';

  function rotulos(tabla) {
    const fila = tabla.tHead && tabla.tHead.rows[tabla.tHead.rows.length - 1];
    if (!fila) return null;
    const lista = [];
    for (const th of fila.cells) {
      for (let i = 0; i < (th.colSpan || 1); i++) lista.push(i === 0 ? th.textContent.trim() : '');
    }
    return lista;
  }

  function convertirEnTarjetas(tabla) {
    const lista = rotulos(tabla);
    // Con campos en el encabezado (filtros) o en las filas (planillas que se editan) no sirve como tarjeta
    if (!lista || lista.length < 4 || tabla.tHead.querySelector('input, select')) return false;
    if (tabla.tBodies[0] && tabla.tBodies[0].querySelector('input:not([type=hidden]):not([type=checkbox]), select, textarea')) return false;
    tabla.classList.add('tabla-tarjetas');
    // Filas de datos y de totales
    const filas = [...tabla.tBodies].flatMap(tb => [...tb.rows]).concat(tabla.tFoot ? [...tabla.tFoot.rows] : []);
    for (const tr of filas) {
      if (tr.cells.length === 1) { tr.classList.add('fila-unica'); continue; } // "No hay registros…"
      let col = 0;
      for (const td of tr.cells) {
        const rotulo = lista[col] || '';
        if (rotulo && !td.hasAttribute('data-label')) td.setAttribute('data-label', rotulo);
        // Columna agrupada bajo el mismo título (p. ej. Dirección: calle, número, piso): sigue alineada al dato
        if (!rotulo && col > 0) td.classList.add('celda-continua');
        // Celda de acciones: solo botones o enlaces con ícono (sin contar scripts)
        const texto = [...td.childNodes].filter(n => n.nodeName !== 'SCRIPT').map(n => n.textContent).join('').trim();
        if (!texto && td.querySelector('a, button, form')) td.classList.add('celda-acciones');
        col += td.colSpan || 1;
      }
    }
    return true;
  }

  function envolverConDesplazamiento(tabla) {
    if (tabla.closest('.table-responsive, .reparto-scroll, [data-desplazamiento]')) return;
    const caja = document.createElement('div');
    caja.className = 'table-responsive';
    caja.setAttribute('data-desplazamiento', '');
    tabla.parentNode.insertBefore(caja, tabla);
    caja.appendChild(tabla);
  }

  function preparar() {
    document.querySelectorAll('.content-wrapper table').forEach(function (tabla) {
      if (tabla.closest(PROPIAS) || tabla.parentElement.closest('table')) return;
      if (!convertirEnTarjetas(tabla)) envolverConDesplazamiento(tabla);
    });
  }

  const TOM_SELECT = 'https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/';

  function cargar(url, tipo) {
    return new Promise(function (listo, error) {
      const el = document.createElement(tipo === 'css' ? 'link' : 'script');
      if (tipo === 'css') { el.rel = 'stylesheet'; el.href = url; } else { el.src = url; }
      el.onload = listo; el.onerror = error;
      document.head.appendChild(el);
    });
  }

  function prepararBuscadores() {
    const selects = document.querySelectorAll('select[data-buscador]');
    if (!selects.length) return;
    Promise.all([cargar(TOM_SELECT + 'css/tom-select.bootstrap4.min.css', 'css'), cargar(TOM_SELECT + 'js/tom-select.complete.min.js', 'js')])
      .then(function () {
        selects.forEach(function (select) {
          const vacia = select.querySelector('option[value=""]');
          new TomSelect(select, {
            maxOptions: null,          // toda la lista, no solo las primeras 50
            // En filtros y campos optativos la opción vacía ("Todos", "Seleccione…") se puede volver a elegir
            allowEmptyOption: !select.required,
            placeholder: vacia ? vacia.textContent.trim() : 'Escriba para buscar…',
            searchField: ['text'],
            render: { no_results: function () { return '<div class="no-results">Sin coincidencias</div>'; } },
          });
        });
      })
      .catch(function () { /* sin conexión al CDN: queda la lista común */ });
  }

  const FORMS_BUSCAR = 'form.form__buscar, form[data-buscar-al-escribir]';

  // Zona de resultados: el primer ancestro del formulario que también contiene el listado
  function zonaResultados(form) {
    let nivel = 0;
    for (let el = form.parentElement; el && el !== document.body; el = el.parentElement, nivel++) {
      if (el.querySelector('table, .pagination, .card-body > p')) return { zona: el, nivel: nivel };
    }
    return null;
  }

  function prepararBusquedaAlEscribir() {
    document.querySelectorAll(FORMS_BUSCAR).forEach(function (form, indice) {
      // Campos de texto con nombre (los calendarios agregan campos de texto sin nombre)
      const TEXTOS = 'input[type=text][name]:not([readonly]), input[type=search][name], input[type=number][name]';
      const destino = zonaResultados(form);
      let espera, pedido;

      async function buscar() {
        if (!destino) return form.submit();
        const url = new URL(form.getAttribute('action') || location.href, location.href);
        url.search = new URLSearchParams(new FormData(form)).toString();
        url.searchParams.delete('_token');
        if (pedido) pedido.abort();
        pedido = new AbortController();
        try {
          const r = await fetch(url, { headers: { Accept: 'text/html' }, signal: pedido.signal });
          // Sesión vencida u otra respuesta inesperada: búsqueda común (lleva al login si hace falta)
          if (!r.ok || r.redirected) return form.submit();
          const doc = new DOMParser().parseFromString(await r.text(), 'text/html');
          let nuevaZona = doc.querySelectorAll(FORMS_BUSCAR)[indice];
          for (let i = 0; nuevaZona && i <= destino.nivel; i++) nuevaZona = nuevaZona.parentElement;
          if (!nuevaZona) return form.submit();
          // Se reemplaza todo lo de la zona menos la rama que contiene el buscador
          const rama = [...destino.zona.children].find(h => h.contains(form));
          const ramaNueva = [...nuevaZona.children].find(h => h.querySelector(FORMS_BUSCAR));
          [...destino.zona.children].forEach(h => { if (h !== rama) h.remove(); });
          const antes = [...nuevaZona.children].slice(0, [...nuevaZona.children].indexOf(ramaNueva));
          const despues = [...nuevaZona.children].slice([...nuevaZona.children].indexOf(ramaNueva) + 1);
          antes.forEach(h => destino.zona.insertBefore(document.adoptNode(h), rama));
          despues.forEach(h => destino.zona.appendChild(document.adoptNode(h)));
          history.replaceState(null, '', url);
          preparar();
        } catch (e) {
          if (e.name !== 'AbortError') form.submit();
        }
      }

      function programar(demora) {
        clearTimeout(espera);
        espera = setTimeout(buscar, demora);
      }

      // Texto: al dejar de tipear (sin listado en la página, se busca con Enter como siempre)
      if (destino) form.querySelectorAll(TEXTOS).forEach(c => c.setAttribute('autocomplete', 'off'));
      form.addEventListener('input', function (e) {
        if (destino && e.target.matches(TEXTOS)) programar(350);
      });
      // Desplegables, casillas y fechas (el calendario deja la fecha en un campo oculto que avisa el cambio)
      form.addEventListener('change', function (e) {
        if (e.target.name && !e.target.matches(TEXTOS + ', input[type=text]')) programar(50);
      });
    });
  }

  function iniciar() {
    preparar();
    prepararBuscadores();
    prepararBusquedaAlEscribir();
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar);
  else iniciar();
})();
