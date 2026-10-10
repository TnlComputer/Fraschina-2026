@extends('adminlte::page')

@section('content_header')
<h1>Agregar Producto a Proveedor</h1>
@stop

@section('content')
<div class="card">
  <div class="card-body">
    <form id="productoForm" action="{{ route('proveedor_producto.store') }}" method="POST">
      @csrf
      <!-- Campo oculto para proveedor_id -->
      <input type="hidden" name="proveedor_id" value="{{ $proveedor->id }}">

      <div class="row">
        <!-- Campo Producto -->
        <div class="col-md-6 mb-3">
          <label for="producto_id" class="form-label">Producto</label>
          <select class="form-control @error('producto_id') is-invalid @enderror" id="producto_id" name="producto_id">
            <option value="">Seleccione un producto</option>
            @foreach($productosAux as $productoAux)
            <option value="{{ $productoAux->id }}" {{ old('producto_id') == $productoAux->id ?
              'selected' : '' }}>
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
            value="{{ old('familia') }}">
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
            name="particularidades">{{ old('particularidades') }}</textarea>
          @error('particularidades')
          <span class="invalid-feedback">{{ $message }}</span>
          @enderror
        </div>
      </div>

      <!-- Botones -->
      <div class="form-acciones mt-3">
        <button type="submit" class="btn btn-primary" id="guardarBtn">Guardar</button>
        <a href="{{ route('proveedor.show', ['proveedor' => $proveedor->id]) }}" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>
@endsection

@section('js')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('productoForm');
    const guardarBtn = document.getElementById('guardarBtn');

    form.addEventListener('submit', function (e) {
      if (!guardarBtn) {
        e.preventDefault(); // Previene el envío si no se da clic en "Guardar"
      }
    });
  });
</script>
@stop