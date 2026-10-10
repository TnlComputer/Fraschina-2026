@extends('adminlte::page')


@section('content_header')
<h1>Nuevo Producto</h1>
@endsection

@section('content')
<div class="card">
  <div class="card-body">
    <form action="{{ route('representacion_producto.store') }}" method="POST">
      @csrf

      <!-- Representación ID (oculto) -->
      <input type="hidden" name="representacion_id" value="{{ $representacion->id }}">

      <!-- Selección de Producto -->
      <div class="form-group">
        <label for="producto_id">Producto</label>
        <select name="producto_id" id="producto_id" class="form-control form-control-sm" required>
          <option value="">-- Seleccionar Producto --</option>
          @foreach($productos as $producto)
          <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
          @endforeach
        </select>
      </div>

      <!-- Campos en filas de 3 columnas -->
      <div class="row">
        <div class="col-md-4">
          <label for="pl">PL</label>
          <input type="text" name="pl" id="pl" class="form-control form-control-sm" placeholder="PL">
        </div>
        <div class="col-md-4">
          <label for="P">P</label>
          <input type="text" name="P" id="P" class="form-control form-control-sm" placeholder="P">
        </div>
        <div class="col-md-4">
          <label for="l">L</label>
          <input type="text" name="l" id="l" class="form-control form-control-sm" placeholder="L">
        </div>
      </div>

      <div class="row mt-2">
        <div class="col-md-4">
          <label for="w">W</label>
          <input type="text" name="w" id="w" class="form-control form-control-sm" placeholder="W">
        </div>
        <div class="col-md-4">
          <label for="humedad">Humedad</label>
          <input type="text" name="humedad" id="humedad" class="form-control form-control-sm" placeholder="Humedad">
        </div>
        <div class="col-md-4">
          <label for="cenizas">Cenizas</label>
          <input type="text" name="cenizas" id="cenizas" class="form-control form-control-sm" placeholder="Cenizas">
        </div>
      </div>

      <div class="row mt-2">
        <div class="col-md-4">
          <label for="glutenhumedo">Gluten Húmedo</label>
          <input type="text" name="glutenhumedo" id="glutenhumedo" class="form-control form-control-sm"
            placeholder="Gluten Húmedo">
        </div>
        <div class="col-md-4">
          <label for="glutenseco">Gluten Seco</label>
          <input type="text" name="glutenseco" id="glutenseco" class="form-control form-control-sm"
            placeholder="Gluten Seco">
        </div>
        <div class="col-md-4">
          <label for="fn">FN</label>
          <input type="text" name="fn" id="fn" class="form-control form-control-sm" placeholder="FN">
        </div>
      </div>

      <div class="row mt-2">
        <div class="col-md-4">
          <label for="estabilidad">Estabilidad</label>
          <input type="text" name="estabilidad" id="estabilidad" class="form-control form-control-sm"
            placeholder="Estabilidad">
        </div>
        <div class="col-md-4">
          <label for="absorcion">Absorción</label>
          <input type="text" name="absorcion" id="absorcion" class="form-control form-control-sm"
            placeholder="Absorción">
        </div>
        <div class="col-md-4">
          <label for="puntuaciones">Puntuaciones</label>
          <input type="text" name="puntuaciones" id="puntuaciones" class="form-control form-control-sm"
            placeholder="Puntuaciones">
        </div>
      </div>

      <div class="form-group mt-2">
        <label for="particularidades">Particularidades</label>
        <textarea name="particularidades" id="particularidades" class="form-control form-control-sm" rows="3"
          placeholder="Describa las particularidades"></textarea>
      </div>

      <!-- Estado (Oculto) -->
      <input type="hidden" name="status" value="A">

      <!-- Botón para guardar y cancelar en la misma línea -->
      <div class="form-acciones mt-3">
        <button type="submit" class="btn btn-primary">Nuevo Producto</button>
        <a href="{{ route('representacion.show', $representacion->id) }}" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>
@endsection