@extends('adminlte::page')

@php $titulo = $producto->exists ? 'Editar producto de Distribución' : 'Nuevo producto de Distribución'; @endphp

@section('title', $titulo)

@section('content_header')
<h1>{{ $titulo }}</h1>
@endsection

@section('content')
<form method="POST" action="{{ $producto->exists ? route('distribucion_catalogo.update', $producto->id) : route('distribucion_catalogo.store') }}">
  @csrf
  @if ($producto->exists) @method('PUT') @endif
  <div class="card" style="max-width: 640px">
    <div class="card-body form-row">
      <div class="form-group col-md-8">
        <label for="productoCDA">Producto <span class="text-danger">*</span></label>
        <input type="text" name="productoCDA" id="productoCDA" maxlength="150" required autofocus
          class="form-control @error('productoCDA') is-invalid @enderror" value="{{ old('productoCDA', $producto->productoCDA) }}">
        @error('productoCDA')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="form-group col-md-4">
        <label for="ivacda">IVA <span class="text-danger">*</span></label>
        <select name="ivacda" id="ivacda" class="form-control">
          @foreach (['10.5' => '10,5 %', '21' => '21 %'] as $valor => $texto)
          <option value="{{ $valor }}" @selected((float) old('ivacda', $producto->porcentajeIva()) === (float) $valor)>{{ $texto }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="card-footer form-acciones">
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
      <a href="{{ route('distribucion_catalogo.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
  </div>
</form>
@endsection
