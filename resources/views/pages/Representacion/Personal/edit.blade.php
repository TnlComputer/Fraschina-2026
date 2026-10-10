@extends('adminlte::page')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
          <h3 class="card-title">Editar Personal de Representación</h3>
        </div>
        <div class="card-body">
          <form action="{{ route('representacion_personal.update', $personal->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
              <!-- Nombre -->
              <div class="col-md-6 mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror"
                  value="{{ old('nombre', $personal->nombre) }}" required>
                @error('nombre')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- Apellido -->
              <div class="col-md-6 mb-3">
                <label for="apellido" class="form-label">Apellido</label>
                <input type="text" name="apellido" id="apellido"
                  class="form-control @error('apellido') is-invalid @enderror"
                  value="{{ old('apellido', $personal->apellido) }}" required>
                @error('apellido')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- Representación -->
              <div class="col-md-6 mb-3">
                <label for="representacion_id" class="form-label">Representación</label>
                <select name="representacion_id" id="representacion_id"
                  class="form-select @error('representacion_id') is-invalid @enderror">
                  <option value="">Seleccione una representación</option>
                  @foreach ($representaciones as $representacion)
                  <option value="{{ $representacion->id }}" {{ old('representacion_id', $personal->representacion_id) ==
                    $representacion->id ? 'selected' : '' }}>
                    {{ $representacion->nombre }}
                  </option>
                  @endforeach
                </select>
                @error('representacion_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- Área -->
              <div class="col-md-6 mb-3">
                <label for="area_id" class="form-label">Área</label>
                <select name="area_id" id="area_id" class="form-select">
                  <option value="">Seleccione un área</option>
                  @foreach ($areas as $area)
                  <option value="{{ $area->id }}" {{ old('area_id', $personal->area_id) == $area->id ? 'selected' : ''
                    }}>
                    {{ $area->nombre }}
                  </option>
                  @endforeach
                </select>
              </div>

              <!-- Cargo -->
              <div class="col-md-6 mb-3">
                <label for="cargo_id" class="form-label">Cargo</label>
                <select name="cargo_id" id="cargo_id" class="form-select">
                  <option value="">Seleccione un cargo</option>
                  @foreach ($cargos as $cargo)
                  <option value="{{ $cargo->id }}" {{ old('cargo_id', $personal->cargo_id) == $cargo->id ? 'selected' :
                    '' }}>
                    {{ $cargo->nombre }}
                  </option>
                  @endforeach
                </select>
              </div>

              <!-- Fuera (checkbox) -->
              <div class="col-md-6 mb-3">
                <label for="fuera" class="form-label">¿Está fuera?</label>
                <input type="checkbox" name="fuera" id="fuera" class="" {{ old('fuera', $personal->fuera) == 1 ?
                'checked' : '' }}>
                @error('fuera')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- Estado -->
              <div class="col-md-6 mb-3">
                <label for="status" class="form-label">Estado</label>
                <select name="status" id="status" class="form-select">
                  <option value="A" {{ old('status', $personal->status) == 'A' ? 'selected' : '' }}>Activo</option>
                  <option value="D" {{ old('status', $personal->status) == 'D' ? 'selected' : '' }}>Desactivado</option>
                </select>
                @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

            </div>

            <div class="form-acciones mt-3">
              <button type="submit" class="btn btn-primary">Guardar Cambios</button>
              <a href="{{ route('representacion.show', $personal->representacion_id ) }}"
                class="btn btn-outline-secondary">Cancelar</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection