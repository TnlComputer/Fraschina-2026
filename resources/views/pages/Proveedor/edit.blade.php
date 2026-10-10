@extends('adminlte::page')

@section('content_header')
<h1>Editar Proveedor</h1>
@stop

@section('content')
<div class="card">
  <div class="card-body">
    <form action="{{ route('proveedor.update', $proveedor->id) }}" method="POST" id="proveedorForm">
      @csrf
      @method('PUT')

      @include('pages.Proveedor.form', ['action' => 'edit', 'proveedor' => $proveedor])

      <div class="form-acciones mt-3">
        <button type="submit" class="btn btn-primary" id="submitBtn">Guardar</button>
        <a href="{{ route('proveedor.index') }}" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('proveedorForm');
    const submitButton = document.getElementById('submitBtn');
    
    // Prevenir el submit al presionar "Enter" en los campos de texto
    form.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault(); // Evita el submit cuando presionas Enter
      }
    });
    
    // Permitir el submit solo cuando el botón "Guardar" es clickeado
    submitButton.addEventListener('click', () => {
      form.submit();
    });
  });
</script>

@endsection