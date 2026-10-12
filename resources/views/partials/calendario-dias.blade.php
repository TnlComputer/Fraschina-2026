{{--
  Calendarios de filtro que marcan los días con datos.
  Uso: <input type="date" name="..." data-dias="agenda"> (fuentes en CalendarioController) e incluir este parcial.
  Opcional: data-dias-enviar → envía el formulario al elegir un día.
  En el celular también se usa este calendario (disableMobile): el nativo del teléfono no puede marcar días.
--}}
@once
@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
<style>
  .flatpickr-day { position: relative; }
  .flatpickr-day.con-datos { font-weight: 800; color: #1f2d3d; }
  .flatpickr-day.con-datos::after {
    content: ''; position: absolute; left: 50%; bottom: 3px; transform: translateX(-50%);
    width: 4px; height: 4px; border-radius: 50%; background: #28a745;
  }
  .flatpickr-day.con-datos.selected::after { background: #fff; }
  /* En pantallas angostas el calendario ocupa el ancho disponible */
  @media (max-width: 575.98px) {
    .flatpickr-calendar.open { max-width: calc(100vw - 16px); }
  }
</style>
@endpush
@push('js')
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/es.js"></script>
<script>
  (function () {
    const url = @json(route('calendario.dias'));
    const ymd = (d) => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    const cache = {};   // fuente => { dias: {}, meses: Set }

    async function cargarMes(fuente, fp) {
      const c = cache[fuente] = cache[fuente] || { dias: {}, meses: new Set() };
      const clave = fp.currentYear + '-' + fp.currentMonth;
      if (c.meses.has(clave)) return fp.redraw();
      c.meses.add(clave);
      // Incluye los días de los meses vecinos que se ven en la grilla
      const desde = ymd(new Date(fp.currentYear, fp.currentMonth, -6));
      const hasta = ymd(new Date(fp.currentYear, fp.currentMonth + 1, 7));
      try {
        const r = await fetch(`${url}?fuente=${encodeURIComponent(fuente)}&desde=${desde}&hasta=${hasta}`, { headers: { Accept: 'application/json' } });
        if (!r.ok) throw new Error(r.status);
        Object.assign(c.dias, await r.json());
      } catch (e) {
        c.meses.delete(clave);
      }
      fp.redraw();
    }

    document.querySelectorAll('input[type=date][data-dias]').forEach(function (input) {
      const fuente = input.dataset.dias;
      const enviar = input.hasAttribute('data-dias-enviar');
      flatpickr(input, {
        locale: 'es',
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd/m/Y',
        altInputClass: input.className,
        allowInput: true,
        disableMobile: true,
        onReady: (s, t, fp) => cargarMes(fuente, fp),
        onOpen: (s, t, fp) => cargarMes(fuente, fp),
        onMonthChange: (s, t, fp) => cargarMes(fuente, fp),
        onYearChange: (s, t, fp) => cargarMes(fuente, fp),
        onDayCreate: (s, t, fp, dia) => {
          const n = (cache[fuente] || { dias: {} }).dias[ymd(dia.dateObj)];
          if (n) {
            dia.classList.add('con-datos');
            dia.title = n + (n > 1 ? ' registros' : ' registro');
          }
        },
        onChange: (s, str) => { if (enviar && str && input.form) input.form.submit(); },
      });
    });
  })();
</script>
@endpush
@endonce
