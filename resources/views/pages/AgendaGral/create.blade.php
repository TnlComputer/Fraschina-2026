@extends('adminlte::page')

@section('content')
<div class="container-fluid">
  <div class="py-4">
    <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 text-gray-900">
          <h2>Nuevo Registro</h2>
          <form id="registroForm" action="{{ route('AgendaGral.store') }}" method="POST">
            @csrf
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="Nombre" class="form-label">Nombre</label>
                <input type="text" name="Nombre" id="Nombre" class="form-control" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="Apellido" class="form-label">Apellido</label>
                <input type="text" name="Apellido" id="Apellido" class="form-control" required>
              </div>
            </div>

            <div class="mb-3">
              <label for="Empresa_Institucion" class="form-label">Empresa/Institución</label>
              <input type="text" name="Empresa_Institucion" id="Empresa_Institucion" class="form-control">
            </div>

            <div class="mb-3">
              <label for="cod_prof" class="form-label">Profesión/Especialidad/Oficio</label>
              <select name="cod_prof" id="cod_prof" class="form-select">
                @foreach ($profesiones as $profesion)
                <option value="{{ $profesion->id }}">{{ sin_dato($profesion->nombreprofesion) }}</option>
                @endforeach
              </select>
            </div>

            <div class="row">
              <div class="col-md-4 mb-3">
                <label for="Tel_Particular" class="form-label">Teléfono Particular</label>
                <input type="text" name="Tel_Particular" id="Tel_Particular" class="form-control">
              </div>
              <div class="col-md-4 mb-3">
                <label for="Tel_Laboral" class="form-label">Teléfono Laboral</label>
                <input type="text" name="Tel_Laboral" id="Tel_Laboral" class="form-control">
              </div>
              <div class="col-md-4 mb-3">
                <label for="Interno" class="form-label">Interno</label>
                <input type="text" name="Interno" id="Interno" class="form-control">
              </div>
            </div>

            <div class="row">
              <div class="col-md-4 mb-3">
                <label for="Celular" class="form-label">Celular</label>
                <input type="text" name="Celular" id="Celular" class="form-control">
              </div>
              <div class="col-md-4 mb-3">
                <label for="Mail" class="form-label">Correo Electrónico</label>
                <input type="email" name="Mail" id="Mail" class="form-control">
              </div>
              <div class="col-md-4 mb-3">
                <label for="Direccion" class="form-label">Dirección</label>
                <input type="text" name="Direccion" id="Direccion" class="form-control">
              </div>
            </div>

            <div class="mb-3">
              <label for="Observaciones" class="form-label">Observaciones</label>
              <textarea name="Observaciones" id="Observaciones" class="form-control"></textarea>
            </div>

            <div class="form-acciones mt-3">
              <button type="submit" class="btn btn-primary">Guardar</button>
              <a href="{{ route('AgendaGral.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

@section('js')
<script>
  // Prevenir que el formulario se envíe al presionar 'Enter'
  document.getElementById('registroForm').addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
      event.preventDefault(); // Prevenir que el formulario se envíe
    }
  });

  // También puedes agregar un comportamiento de confirmación en el botón guardar si lo deseas
  document.querySelector('button[type="submit"]').addEventListener('click', function(event) {
    // Aquí puedes agregar lógica adicional si es necesario
  });
</script>
@endsection

@endsection