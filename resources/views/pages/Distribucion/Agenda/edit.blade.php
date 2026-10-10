@extends('adminlte::page')

@section('title', 'Editar Agenda')

@section('content_header')
<h1>Editar Agenda</h1>
@stop

@section('content')
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Formulario de Edición de Agenda</h3>
  </div>
  <div class="card-body">
    <form action="{{ route('distribucion_agenda.update', $agenda->id) }}" method="POST">
      @csrf
      @method('PUT')
      <div class="form-row">
        <div class="form-group col-md-4">
          <label for="distribucion_id">Distribución</label>
          <select name="distribucion_id" id="distribucion_id" class="form-control">
            <option value="">Seleccione una Distribución</option>
            @foreach($distribuciones as $distribucion)
            <option value="{{ $distribucion->id }}" {{ old('distribucion_id', $agenda->distribucion_id) ==
              $distribucion->id ?
              'selected' : '' }}>
              @if(!empty($distribucion->nomfantasia) && !empty($distribucion->razonsocial))
              {{ unir($distribucion->nomfantasia, $distribucion->razonsocial) }}
              @elseif(!empty($distribucion->nomfantasia))
              {{ $distribucion->nomfantasia }}
              @elseif(!empty($distribucion->razonsocial))
              {{ $distribucion->razonsocial }}
              @endif
            </option>
            @endforeach
          </select>
        </div>

        <!-- Campo: Fecha -->
        <div class="form-group col-md-2">
          <label for="fecha">Fecha</label>
          <input type="date" name="fecha" id="fecha" class="form-control @error('fecha') is-invalid @enderror"
            value="{{ old('fecha', $agenda->fecha) }}" required>
          @error('fecha')
          <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>

        <!-- Campo: Hora -->
        <div class="form-group col-md-2">
          <label for="hs">Hora</label>
          <input type="time" name="hs" id="hs" class="form-control @error('hs') is-invalid @enderror"
            value="{{ old('hs', $agenda->hs) }}" required>
          @error('hs')
          <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>
        {{--
      </div> --}}

      <!-- Campos relacionados con tablas auxiliares en la misma línea -->
      @php
      $auxFields = [
      ['name' => 'prioridad_id', 'label' => 'Prioridad', 'options' => $prioridades, 'display' => 'nombre'],
      ['name' => 'accion_id', 'label' => 'Acción', 'options' => $acciones, 'display' => 'accion'],
      ];
      @endphp

      {{-- <div class=""> --}}
        @foreach ($auxFields as $field)
        <div class="form-group col-md-2">
          <label for="{{ $field['name'] }}">{{ $field['label'] }}</label>
          <select name="{{ $field['name'] }}" id="{{ $field['name'] }}"
            class="form-control @error($field['name']) is-invalid @enderror">
            <option value="">Seleccione {{ $field['label'] }}</option>
            @foreach ($field['options'] as $option)
            <option value="{{ $option->id }}" {{ old($field['name'], $agenda->{$field['name']}) == $option->id ?
              'selected' : '' }}>
              {{ trim((string) $option->{$field['display']}) !== '' ? $option->{$field['display']} : '(sin dato)' }}
            </option>
            @endforeach
          </select>
          @error($field['name'])
          <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>
        @endforeach

        <!-- Lista de Personal -->
        <div class="form-group col-md-3">
          <label for="persona_id">Persona</label>
          <select name="persona_id" id="persona_id" class="form-control">
            <option value="">Seleccione una Persona</option>
            @foreach($personal as $persona)
            <option value="{{ $persona->id }}" {{ old('persona_id', $agenda->persona_id) == $persona->id ? 'selected' :
              '' }}>
              {{ $persona->nombre }} {{ $persona->apellido }}
            </option>
            @endforeach
          </select> @error('persona_id')
          <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>

        <!-- Campo: Última cotización / venta -->
        <div class="form-group col-md-6">
          <label for="cotizacion">Ult. cotiz./venta</label>
          <input type="text" name="cotizacion" id="cotizacion"
            class="form-control @error('cotizacion') is-invalid @enderror"
            value="{{ old('cotizacion', $agenda->cotizacion) }}">
          @error('cotizacion')
          <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>

        <!-- Campo: Fecha de la última cotización / venta -->
        <div class="form-group col-md-3">
          <label for="fecCot">Fecha ult. cotiz./venta</label>
          <input type="date" name="fecCot" id="fecCot" class="form-control @error('fecCot') is-invalid @enderror"
            value="{{ old('fecCot', $agenda->fecCot ? \Illuminate\Support\Carbon::parse($agenda->fecCot)->format('Y-m-d') : '') }}">
          @error('fecCot')
          <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>
      </div>

      <!-- Campo: Temas -->
      <div class="form-group">
        <label for="temas">Temas</label>
        <textarea name="temas" id="temas"
          class="form-control @error('temas') is-invalid @enderror">{{ old('temas', $agenda->temas) }}</textarea>
        @error('temas')
        <span class="invalid-feedback">{{ $message }}</span>
        @enderror
      </div>

      <div class="form-acciones mt-3">
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('distribucion_agenda.index') }}" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>
@stop

@section('js')
<script>
  document.getElementById('distribucion_id').addEventListener('change', function () {
const distribucionId = this.value;
const personaSelect = document.getElementById('persona_id');

// Limpiar las opciones previas
personaSelect.innerHTML = '<option value="">Seleccione una Persona</option>';

if (distribucionId) {
// Mostrar un indicador de carga (opcional)
const loadingOption = document.createElement('option');
loadingOption.textContent = 'Cargando...';
loadingOption.disabled = true;
personaSelect.appendChild(loadingOption);

// Realizar la solicitud AJAX
fetch(`/distribucion/${distribucionId}/personal`)
.then(response => {
if (!response.ok) {
throw new Error('Error en la solicitud.');
}
return response.json();
})
.then(data => {
// Limpiar las opciones nuevamente
personaSelect.innerHTML = '<option value="">Seleccione una Persona</option>';
if (data.length === 0) {
const noDataOption = document.createElement('option');
noDataOption.textContent = 'No hay personas disponibles.';
noDataOption.disabled = true;
personaSelect.appendChild(noDataOption);
} else {
// Agregar las opciones al select de persona
data.forEach(persona => {
const option = document.createElement('option');
option.value = persona.id;
option.textContent = `${persona.nombre} ${persona.apellido}`;
personaSelect.appendChild(option);
});
}
})
.catch(error => {
console.error('Error al cargar el personal:', error);
personaSelect.innerHTML = '<option value="">Error al cargar personas.</option>';
});
}
});
</script>
@stop