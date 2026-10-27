@extends('adminlte::page')

@section('content')
<div class="container mt-4">
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Editar Producto de la Representación</h3>
      <a href="{{ route('representacion.show', $representacion->id) }}"
        class="btn btn-secondary btn-sm float-right">Regresar</a>
    </div>
    <div class="card-body">
      <form action="{{ route('representacion_producto.update', $producto->id) }}" method="POST" id="editForm">
        @csrf
        @method('PUT')

        <!-- Representación ID (oculto) -->
        <input type="hidden" name="representacion_id" value="{{ $representacion->id }}">

        <!-- Selección de Producto -->
        <div class="form-group">
          <label for="producto_id">Producto</label>
          <select name="producto_id" id="producto_id" class="form-control form-control-sm" required data-buscador>
            <option value="">-- Seleccionar Producto --</option>
            @foreach($productos as $prod)
            <option value="{{ $prod->id }}" {{ $producto->producto_id == $prod->id ? 'selected' : '' }}>
              {{ $prod->nombre }}
            </option>
            @endforeach
          </select>
        </div>

        <!-- Campos en filas de 3 columnas -->
        <div class="row">
          <div class="col-md-4">
            <label for="pl">PL</label>
            <input type="text" name="pl" id="pl" class="form-control form-control-sm"
              value="{{ old('pl', $producto->pl) }}" placeholder="PL">
          </div>
          <div class="col-md-4">
            <label for="P">P</label>
            <input type="text" name="P" id="P" class="form-control form-control-sm" value="{{ old('P', $producto->P) }}"
              placeholder="P">
          </div>
          <div class="col-md-4">
            <label for="l">L</label>
            <input type="text" name="l" id="l" class="form-control form-control-sm" value="{{ old('l', $producto->l) }}"
              placeholder="L">
          </div>
        </div>

        <div class="row mt-2">
          <div class="col-md-4">
            <label for="w">W</label>
            <input type="text" name="w" id="w" class="form-control form-control-sm" value="{{ old('w', $producto->w) }}"
              placeholder="W">
          </div>
          <div class="col-md-4">
            <label for="humedad">Humedad</label>
            <input type="text" name="humedad" id="humedad" class="form-control form-control-sm"
              value="{{ old('humedad', $producto->humedad) }}" placeholder="Humedad">
          </div>
          <div class="col-md-4">
            <label for="cenizas">Cenizas</label>
            <input type="text" name="cenizas" id="cenizas" class="form-control form-control-sm"
              value="{{ old('cenizas', $producto->cenizas) }}" placeholder="Cenizas">
          </div>
        </div>

        <div class="row mt-2">
          <div class="col-md-4">
            <label for="glutenhumedo">Gluten Húmedo</label>
            <input type="text" name="glutenhumedo" id="glutenhumedo" class="form-control form-control-sm"
              value="{{ old('glutenhumedo', $producto->glutenhumedo) }}" placeholder="Gluten Húmedo">
          </div>
          <div class="col-md-4">
            <label for="glutenseco">Gluten Seco</label>
            <input type="text" name="glutenseco" id="glutenseco" class="form-control form-control-sm"
              value="{{ old('glutenseco', $producto->glutenseco) }}" placeholder="Gluten Seco">
          </div>
          <div class="col-md-4">
            <label for="fn">FN</label>
            <input type="text" name="fn" id="fn" class="form-control form-control-sm"
              value="{{ old('fn', $producto->fn) }}" placeholder="FN">
          </div>
        </div>

        <div class="row mt-2">
          <div class="col-md-4">
            <label for="estabilidad">Estabilidad</label>
            <input type="text" name="estabilidad" id="estabilidad" class="form-control form-control-sm"
              value="{{ old('estabilidad', $producto->estabilidad) }}" placeholder="Estabilidad">
          </div>
          <div class="col-md-4">
            <label for="absorcion">Absorción</label>
            <input type="text" name="absorcion" id="absorcion" class="form-control form-control-sm"
              value="{{ old('absorcion', $producto->absorcion) }}" placeholder="Absorción">
          </div>
          <div class="col-md-4">
            <label for="puntuaciones">Puntuaciones</label>
            <input type="text" name="puntuaciones" id="puntuaciones" class="form-control form-control-sm"
              value="{{ old('puntuaciones', $producto->puntuaciones) }}" placeholder="Puntuaciones">
          </div>
        </div>

        <div class="form-group mt-2">
          <label for="particularidades">Particularidades</label>
          <textarea name="particularidades" id="particularidades" class="form-control form-control-sm" rows="3"
            placeholder="Describa las particularidades">{{ old('particularidades', $producto->particularidades) }}</textarea>
        </div>

        <!-- Estado (Oculto) -->
        <input type="hidden" name="status" value="A">

        <!-- Botón para actualizar -->
        <div class="form-group mt-3 text-right">
          <button type="submit" class="btn btn-primary btn-sm">Actualizar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Script para prevenir el envío con Enter -->
<script>
  document.getElementById('editForm').addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
      event.preventDefault(); // Previene el envío del formulario
    }
  });
</script>
@endsection