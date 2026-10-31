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
            @include('pages.Expedicion.General.form')
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
