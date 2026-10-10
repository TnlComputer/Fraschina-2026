@extends('adminlte::page')

@section('title', 'Crear Proveedor Personal')

@section('content_header')
<h1>Crear Proveedor Personal</h1>
@stop

@section('content')
<div class="container">
  <form id="proveedorPersonalForm" action="{{ route('proveedor_personal.store') }}" method="POST">
    @csrf
    @include('pages.Proveedor.Personal.form')

    <div class="form-acciones mt-3">
      <button type="submit" class="btn btn-primary" id="saveButton">Guardar</button>
      <a href="{{ route('proveedor.show', ['proveedor' => $personal->proveedor_id]) }}" class="btn btn-outline-secondary"
        id="cancelButton">Cancelar</a>
    </div>
  </form>
</div>

@section('js')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('proveedorPersonalForm');
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