@extends('adminlte::page')

@section('title', 'Crear Agro')

@section('content_header')
<h1>Crear Agro</h1>
@stop

@section('content')
<div class="card">
  <div class="card-body">
    <form id="agroForm" action="{{ route('agro.store') }}" method="POST">
      @csrf
      @include('pages.Agro.form')

      <div class="form-acciones mt-3">
        <button type="submit" id="submitButton" class="btn btn-primary">Guardar</button>
        <a href="{{ route('agro.index') }}" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>
@endsection

@section('js')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('agroForm');
    const submitButton = document.getElementById('submitButton');

    let isSubmitButtonClicked = false;

    // Detectar clic en el botón de submit
    submitButton.addEventListener('click', () => {
      isSubmitButtonClicked = true;
    });

    // Interceptar el envío del formulario
    form.addEventListener('submit', (event) => {
      if (!isSubmitButtonClicked) {
        event.preventDefault(); // Prevenir envío
        alert('Por favor, utiliza el botón de Guardar o Actualizar para enviar el formulario.');
      }
      isSubmitButtonClicked = false; // Resetear estado
    });
  });
</script>
@endsection