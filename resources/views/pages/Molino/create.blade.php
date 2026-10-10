@extends('adminlte::page')

@section('content_header')
<h1>Crear Molino</h1>
@stop

@section('content')
<div class="card">
  <div class="card-body">
    <form action="{{ route('molino.store') }}" method="POST" id="molinoForm">
      @csrf

      @include('pages.Molino.form', ['action' => 'create'])
      <div class="form-acciones mt-3">
        <button type="submit" class="btn btn-primary" id="submitBtn">Guardar</button>
        <a href="{{ route('molino.index') }}" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('molinoForm');
    const submitButton = document.getElementById('submitBtn');

    // Prevenir el submit al presionar "Enter" en los campos de texto
    form.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault(); // Evita el submit cuando presionas Enter
      }
    });

    // Permitir el submit solo cuando el botón "Guardar" es clickeado
    submitButton.addEventListener('click', (e) => {
      form.submit();
    });
  });
</script>

@endsection