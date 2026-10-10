@extends('adminlte::page')

@section('title', 'Editar Agro')

@section('content_header')
<h1>Editar Agro</h1>
@stop

@section('content')
<div class="card">
  <div class="card-body">
    <form action="{{ route('agro.update', $agro->id) }}" method="POST">
      @csrf
      @method('PUT')
      @include('pages.Agro.form', ['agro' => $agro])

      <div class="form-acciones mt-3">
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('agro.index') }}" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>

@endsection

@section('js')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form'); // Selecciona el formulario
    const submitButton = form.querySelector('button[type="submit"]'); // Botón de envío

    // Añade un atributo personalizado al botón
    submitButton.dataset.clicked = "false";

    // Marca el botón como clicado al hacer clic
    submitButton.addEventListener('click', () => {
      submitButton.dataset.clicked = "true";
    });

    // Intercepta el evento de envío del formulario
    form.addEventListener('submit', (event) => {
      if (submitButton.dataset.clicked === "false") {
        event.preventDefault(); // Evita el envío
        alert('Por favor, utiliza el botón "Actualizar" para enviar el formulario.');
      }

      // Resetea el estado después de un envío
      submitButton.dataset.clicked = "false";
    });
  });
</script>
@endsection