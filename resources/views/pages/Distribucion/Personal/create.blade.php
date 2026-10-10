@extends('adminlte::page')

@section('title', 'Crear Distribución Personal')

@section('content_header')
<h1>Crear Distribución Personal</h1>
@stop

@section('content')
<div class="container">
  <form action="{{ route('distribucion_personal.store') }}" method="POST">
    @csrf
    @include('pages.Distribucion.Personal.form')

    <div class="form-acciones mt-3">
      <button type="submit" class="btn btn-primary">Guardar</button>
      <a href="{{ route('distribucion.show', ['distribucion' => $personal->distribucion_id]) }}"
        class="btn btn-outline-secondary">Cancelar</a>
    </div>
  </form>
</div>

@section('js')
<script>
  // Deshabilitar el envío del formulario al presionar "Enter" excepto cuando el foco está en los botones
    document.addEventListener('DOMContentLoaded', function () {
        // Seleccionamos el formulario
        const form = document.querySelector('form');
        
        // Seleccionamos los botones de guardar y cancelar
        const saveButton = document.querySelector('button[type="submit"]');
        const cancelButton = document.querySelector('a.btn-secondary');

        // Función para prevenir el submit
        function preventEnter(event) {
            // Verificar si el enfoque no está en un botón
            if (event.key === 'Enter' && document.activeElement !== saveButton && document.activeElement !== cancelButton) {
                event.preventDefault();
            }
        }

        // Añadir el listener para prevenir el enter en todo el formulario
        form.addEventListener('keydown', preventEnter);
    });
</script>
@stop
@endsection