@extends('adminlte::page')

@section('title', 'Nuevo personal')

@section('content_header')
<h1>Nuevo personal</h1>
@endsection

@section('content')
<div class="card">
  <div class="card-body">
    <form action="{{ route('representacion_personal.store') }}" method="POST">
      @csrf
      @include('pages.Representacion.Personal.form')

      <div class="form-acciones mt-3">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
        <a href="{{ route('representacion.show', $representacion->id) }}" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>
@endsection
