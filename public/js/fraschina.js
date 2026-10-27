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
            allowEmptyOption: false,
            placeholder: vacia ? vacia.textContent.trim() : 'Escriba para buscar…',
            searchField: ['text'],
            render: { no_results: function () { return '<div class="no-results">Sin coincidencias</div>'; } },
          });
        });
      })
      .catch(function () { /* sin conexión al CDN: queda la lista común */ });
  }

  function iniciar() {
    preparar();
    prepararBuscadores();
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar);
  else iniciar();
})();
