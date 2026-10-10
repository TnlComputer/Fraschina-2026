@extends('adminlte::page')

@section('title', 'Crear Molino Personal')

@section('content_header')
<h1>Crear Molino Personal</h1>
@stop

@section('content')
<div class="container">
  <form id="molinoPersonalForm" action="{{ route('molino_personal.store') }}" method="POST">
    @csrf
    @include('pages.Molino.Personal.form')

    <div class="form-acciones mt-3">
      <button type="submit" class="btn btn-primary" id="saveButton">Guardar</button>
      <a href="{{ route('molino.show', ['molino' => $personal->molino_id]) }}" class="btn btn-outline-secondary"
        id="cancelButton">Cancelar</a>
    </div>
  </form>
</div>

@section('js')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('molinoPersonalForm');
    const saveButton = document.getElementById('saveButton');
    const cancelButton = document.getElementById('cancelButton');

    // Prevenir "Enter" en el formulario
    form.addEventListener('keydown', function (event) {
      // Permitir "Enter" en campos tipo `<textarea>` o cuando el foco está en los botones
      if (
        event.key === 'Enter' &&
        event.target.tagName !== 'TEXTAREA' &&
        document.activeElement !== saveButton &&
        document.activeElement !== cancelButton
      ) {
        event.preventDefault();
      }
    });
  });
</script>
@stop
@endsection