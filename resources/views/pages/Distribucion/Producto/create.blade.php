@extends('adminlte::page')

@section('content_header')
<h1>Agregar Producto a Distribución</h1>
@stop

@section('content')
<div class="card">
  <div class="card-body">
    <form action="{{ route('distribucion_producto.store') }}" method="POST">
      @csrf
      <!-- Campo oculto para distribucion_id -->
      <input type="hidden" name="distribucion_id" value="{{ $distribucion_id }}">

      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="producto_id" class="form-label">Producto</label>
          <select class="form-control" id="producto_id" name="producto_id">
            <option value="">Seleccione un producto</option>
            @foreach($productos as $producto)
            <option value="{{ $producto->id }}" {{ old('producto_id')==$producto->id ? 'selected' : '' }}>
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
          <input type="number" step="0.01" class="form-control" id="precio" name="precio" value="{{ old('precio') }}">
          @error('precio')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>

        <div class="col-md-6 mb-3">
          <label for="fecha" class="form-label">Fecha Precio</label>
          <input type="date" class="form-control" id="fecha" name="fecha" value="{{ old('fecha') }}">
          @error('fecha')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>

        <div class="col-md-6 mb-3">
          <label for="fechaUEnt" class="form-label">Fecha Última Entrega</label>
          <input type="date" class="form-control" id="fechaUEnt" name="fechaUEnt" value="{{ old('fechaUEnt') }}">
          @error('fechaUEnt')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>
      </div>

      <div class="form-acciones mt-3">
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="{{ route('distribucion.show', ['distribucion' => $distribucion_id]) }}"
          class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>
@endsection