@extends('adminlte::page')

@section('content_header')
<h1>Editar Producto en Proveedor</h1>
@stop

@section('content')
<div class="card">
  <div class="card-body">
    <form action="{{ route('proveedor_producto.update', $producto->id) }}" method="POST" id="editForm">
      @csrf
      @method('PUT')

      <!-- Campo oculto para proveedor_id -->
      <input type="hidden" name="proveedor_id" value="{{ $proveedor->id }}">

      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="producto_id" class="form-label">Producto</label>
          <select class="form-control" id="producto_id" name="producto_id" required>
            <option value="">Seleccione un producto</option>
            @foreach($productosAux as $productoAux)
            <option value="{{ $productoAux->id }}" {{ (int) old('producto_id', $producto->producto_id) === (int)
              $productoAux->id ? 'selected' : '' }}>
              {{ $productoAux->nombre }}
            </option>
            @endforeach
          </select>
          @error('producto_id')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>
      </div>

      <!-- Campo Familia -->
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="familia" class="form-label">Familia</label>
          <input type="text" class="form-control @error('familia') is-invalid @enderror" id="familia" name="familia"
            value="{{ old('familia', $producto->familia) }}">
          @error('familia')
          <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>
      </div>

      <!-- Campo Particularidades -->
      <div class="row">
        <div class="col-md-12 mb-3">
          <label for="particularidades" class="form-label">Particularidades</label>
          <textarea class="form-control @error('particularidades') is-invalid @enderror" id="particularidades"
            name="particularidades">{{ old('particularidades', $producto->particularidades) }}</textarea>
          @error('particularidades')
          <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>
      </div>

      <div class="form-acciones mt-3">
        <button type="submit" class="btn btn-primary" id="saveBtn">Guardar</button>
        <a href="{{ route('proveedor.show', ['proveedor' => $proveedor->id]) }}" class="btn btn-outline-secondary"
          id="cancelBtn">Cancelar</a>
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
      alert('Por favor, utilice el botón "Guardar" para enviar el formulario.');
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