@extends('adminlte::page')

@section('content')
<div class="container-fluid">
  <div class="py-4">
    <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 text-gray-900">
          <h2 class="text-center">Editar Registro</h2>
          <form id="registroForm" action="{{ route('AgendaGral.update', $agenda->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
              <!-- Nombre y Apellido -->
              <div class="col-md-6 mb-3">
                <label for="Nombre" class="form-label">Nombre</label>
                <input type="text" name="Nombre" id="Nombre" value="{{ $agenda->nombre }}" class="form-control"
                  required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="Apellido" class="form-label">Apellido</label>
                <input type="text" name="Apellido" id="Apellido" value="{{ $agenda->apellido }}" class="form-control"
                  required>
              </div>
            </div>

            <div class="mb-3">
              <!-- Empresa/Institución -->
              <label for="Empresa_Institucion" class="form-label">Empresa/Institución</label>
              <input type="text" name="Empresa_Institucion" id="Empresa_Institucion"
                value="{{ $agenda->empresa_institucion }}" class="form-control">
            </div>

            <div class="mb-3">
              <!-- Profesión/Especialidad/Oficio -->
              <label for="cod_prof" class="form-label">Profesión/Especialidad/Oficio</label>
              <select name="cod_prof" id="cod_prof" class="form-select">
                @foreach ($profesiones as $profesion)
                <option value="{{ $profesion->id }}" {{ $agenda->cod_prof == $profesion->id ? 'selected' : '' }}>
                  {{ sin_dato($profesion->nombreprofesion) }}
                </option>
                @endforeach
              </select>
            </div>

            <div class="row">
              <!-- Teléfonos -->
              <div class="col-md-4 mb-3">
                <label for="Tel_Particular" class="form-label">Teléfono Particular</label>
                <input type="text" name="Tel_Particular" id="Tel_Particular" value="{{ $agenda->tel_particular }}"
                  class="form-control">
              </div>
              <div class="col-md-4 mb-3">
                <label for="Tel_Laboral" class="form-label">Teléfono Laboral</label>
                <input type="text" name="Tel_Laboral" id="Tel_Laboral" value="{{ $agenda->tel_laboral }}"
                  class="form-control">
              </div>
              <div class="col-md-4 mb-3">
                <label for="Interno" class="form-label">Interno</label>
                <input type="text" name="Interno" id="Interno" value="{{ $agenda->interno }}" class="form-control">
              </div>
            </div>

            <div class="row">
              <!-- Celular, Correo Electrónico y Dirección -->
              <div class="col-md-4 mb-3">
                <label for="Celular" class="form-label">Celular</label>
                <input type="text" name="Celular" id="Celular" value="{{ $agenda->celular }}" class="form-control">
              </div>
              <div class="col-md-4 mb-3">
                <label for="Mail" class="form-label">Correo Electrónico</label>
                <input type="email" name="Mail" id="Mail" value="{{ $agenda->mail }}" class="form-control">
              </div>
              <div class="col-md-4 mb-3">
                <label for="Direccion" class="form-label">Dirección</label>
                <input type="text" name="Direccion" id="Direccion" value="{{ $agenda->direccion }}"
                  class="form-control">
              </div>
            </div>

            <div class="mb-3">
              <!-- Observaciones -->
              <label for="Observaciones" class="form-label">Observaciones</label>
              <textarea name="Observaciones" id="Observaciones"
                class="form-control">{{ $agenda->observaciones }}</textarea>
            </div>

            <!-- Botones -->
            <div class="form-acciones mt-3">
              <button type="submit" class="btn btn-primary">Actualizar</button>
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

  // Confirmar antes de enviar el formulario al presionar el botón "Actualizar"
  document.querySelector('button[type="submit"]').addEventListener('click', function(event) {
    // Aquí puedes agregar lógica adicional si lo deseas
    // Si quieres agregar confirmación antes de enviar el formulario:
    // if (!confirm('¿Estás seguro de que deseas actualizar este registro?')) {
    //     event.preventDefault();
    // }
  });
</script>
@endsection

@endsection