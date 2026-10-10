@extends('adminlte::page')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">{{ __('Nuevo Registro de Expedición') }}</h3>
        </div>
        <div class="card-body">
          <form action="{{ route('expedicion_general.store') }}" method="POST">
            @csrf
            <div class="row">
              <div class="col-md-2">
                <label for="fecha">Fecha</label>
                <input type="date" name="fecha" class="form-control" value="{{ old('fecha') }}" required>
              </div>
              <div class="col-md-2">
                <label for="mo">MO</label>
                <input type="text" name="mo" class="form-control" value="{{ old('mo') }}" maxlength="5" required>
              </div>
              <div class="col-md-2">
                <label for="cl">CL</label>
                <input type="text" name="cl" class="form-control" value="{{ old('cl') }}" maxlength="5" required>
              </div>
              <div class="col-md-2">
                <label for="modo">Modo</label>
                <input type="text" name="modo" class="form-control" value="{{ old('modo') }}">
              </div>
              <div class="col-md-2">
                <label for="prod">Prod</label>
                <input type="text" name="prod" class="form-control" value="{{ old('prod') }}">
              </div>
              <div class="col-md-2">
                <label for="p">P</label>
                <input type="text" name="p" class="form-control" value="{{ old('p') }}">
              </div>
              <!-- Añadir otros campos según necesidad -->
            </div>
            <div class="form-acciones mt-3">
              <button type="submit" class="btn btn-primary">Guardar</button>
              <a href="{{ route('expedicion_general.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection