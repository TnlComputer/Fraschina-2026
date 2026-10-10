@extends('adminlte::page')

@section('title', 'Editar personal')

@section('content_header')
<h1>Editar personal</h1>
@endsection

@section('content')
<div class="card">
  <div class="card-body">
    <form action="{{ route('representacion_personal.update', $personal->id) }}" method="POST">
      @csrf
      @method('PUT')
      @include('pages.Representacion.Personal.form')

      <div class="form-acciones mt-3">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
        <a href="{{ route('representacion.show', $personal->representacion_id) }}" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>
@endsection
