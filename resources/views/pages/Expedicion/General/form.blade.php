{{-- Campos de un certificado de calidad. Fuera de los mínimos y máximos se puede guardar igual; se marca en rojo. --}}
@php
  $registro = $expedicionGeneral ?? null;
  $analisis = ['p' => 'P', 'l' => 'L', 'pl' => 'P/L', 'w' => 'W', 'gh' => 'GH', 'gs' => 'GS', 'gi' => 'GI',
    'hum' => 'Hum', 'cz' => 'Czas', 'est' => 'Est', 'abs' => 'Abs', 'fn' => 'FN', 'punt' => 'Punt'];
  $rangos = \App\Models\ExpedicionGeneral::RANGOS;
  $numero = fn($n) => rtrim(rtrim(number_format($n, 3, ',', ''), '0'), ',');
@endphp
<div class="row">
  <div class="col-md-2">
    <label for="fecha">Fecha</label>
    <input type="date" id="fecha" name="fecha" class="form-control" value="{{ old('fecha', $registro?->fecha) }}" required>
  </div>
  <div class="col-md-2">
    <label for="mo">MO</label>
    <input type="text" id="mo" name="mo" class="form-control" value="{{ old('mo', $registro?->mo) }}" maxlength="5" required>
  </div>
  <div class="col-md-2">
    <label for="cl">CL</label>
    <input type="text" id="cl" name="cl" class="form-control" value="{{ old('cl', $registro?->cl) }}" maxlength="5" required>
  </div>
  <div class="col-md-2">
    <label for="modo">Modo</label>
    <input type="text" id="modo" name="modo" class="form-control" value="{{ old('modo', $registro?->modo) }}">
  </div>
  <div class="col-md-4">
    <label for="prod">Prod</label>
    <input type="text" id="prod" name="prod" class="form-control" value="{{ old('prod', $registro?->prod) }}">
  </div>
</div>
<div class="row mt-2">
  @foreach ($analisis as $campo => $etiqueta)
    @php [$min, $max] = $rangos[$campo]; @endphp
    <div class="col-6 col-md-2 col-lg-1">
      <label for="{{ $campo }}">{{ $etiqueta }}</label>
      <input type="number" step="any" id="{{ $campo }}" name="{{ $campo }}" class="form-control no-spin" data-rango
        data-min="{{ $min }}" data-max="{{ $max }}" value="{{ old($campo, $registro?->{$campo}) }}">
      <small class="text-muted">{{ $numero($min) }} a {{ $numero($max) }}</small>
    </div>
  @endforeach
</div>
<div class="row mt-2">
  <div class="col-12">
    <label for="particularidades">Particularidades</label>
    <input type="text" id="particularidades" name="particularidades" class="form-control" maxlength="255"
      value="{{ old('particularidades', $registro?->particularidades) }}">
  </div>
</div>

<style>
  .no-spin { -moz-appearance: textfield; appearance: textfield; }
  .no-spin::-webkit-inner-spin-button, .no-spin::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
  input[data-rango].fuera-rango { color: #dc3545; font-weight: bold; border-color: #dc3545; }
  input[data-rango].fuera-rango + small { color: #dc3545 !important; }
</style>
<script>
  document.querySelectorAll('input[data-rango]').forEach(function (input) {
    const marcar = function () {
      const valor = parseFloat(input.value);
      const fuera = input.value !== '' && !isNaN(valor) && (valor < parseFloat(input.dataset.min) || valor > parseFloat(input.dataset.max));
      input.classList.toggle('fuera-rango', fuera);
    };
    input.addEventListener('input', marcar);
    marcar();
  });
</script>
