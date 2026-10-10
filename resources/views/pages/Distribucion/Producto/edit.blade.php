@extends('adminlte::page')

@section('content_header')
<h1>Editar Producto en Distribución</h1>
@stop

@section('content')
<div class="card">
  <div class="card-body">
    <form action="{{ route('distribucion_producto.update', $distribucion_producto->id) }}" method="POST" id="editForm">
      @csrf
      @method('PUT')

      <!-- Campo oculto para distribucion_id -->
      <input type="hidden" name="distribucion_id" value="{{ $distribucion_producto->distribucion_id }}">

      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="producto_id" class="form-label">Producto</label>
          <select class="form-control" id="producto_id" name="producto_id">
            <option value="">Seleccione un producto</option>
            @foreach($productos as $producto)
            <option value="{{ $producto->id }}" {{ old('producto_id', $distribucion_producto->producto_id) ==
              $producto->id ? 'selected' : '' }}>
              {{ $producto->productoCDA }}
            </option>
            @endforeach
          </select>
          @error('producto_id')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>

        <div class="col-md-6 mb-3">
          <label for="precio" class="form-label">Precio</label>
          <input type="number" step="0.01" class="form-control" id="precio" name="precio"
            value="{{ old('precio', $distribucion_producto->precio) }}">
          @error('precio')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>

        <div class="col-md-6 mb-3">
          <label for="fecha" class="form-label">Fecha Precio</label>
          <input type="date" class="form-control" id="fecha" name="fecha"
            value="{{ old('fecha', $distribucion_producto->fecha) }}">
          @error('fecha')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>

        <div class="col-md-6 mb-3">
          <label for="fechaUEnt" class="form-label">Fecha Última Entrega</label>
          <input type="date" class="form-control" id="fechaUEnt" name="fechaUEnt"
            value="{{ old('fechaUEnt', $distribucion_producto->fechaUEnt) }}">
          @error('fechaUEnt')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>

        <div class="col-md-6 mb-3">
          <label for="status" class="form-label">Estado</label>
          <select class="form-control" id="status" name="status">
            <option value="A" {{ old('status', $distribucion_producto->status) == 'A' ? 'selected' : '' }}>Activo
            </option>
            <option value="D" {{ old('status', $distribucion_producto->status) == 'D' ? 'selected' : '' }}>Desactivado
            </option>
          </select>
          @error('status')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>
      </div>

      <div class="form-acciones mt-3">
        <button type="submit" class="btn btn-primary" id="saveBtn">Guardar</button>
        <a href="{{ route('distribucion.show', ['distribucion' => $distribucion_producto->distribucion_id]) }}"
          class="btn btn-outline-secondary" id="cancelBtn">Cancelar</a>
      </div>
    </form>
  </div>
</div>
@endsection

@section('js')
<script>
  // Prevent form submission when pressing Enter
  document.getElementById('editForm').addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
      event.preventDefault(); // Prevent form submission when pressing Enter
    }
  });

  document.getElementById('editForm').addEventListener('submit', function(event) {
    // Verifica si se hizo clic en el botón "Guardar"
    if (event.submitter && event.submitter.id !== 'saveBtn') {
      event.preventDefault(); // Evita el envío del formulario si no es el botón "Guardar"
    }
  });
</script>
@endsection