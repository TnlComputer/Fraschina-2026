@extends('adminlte::page')

@section('title', 'Editar cliente')

@section('content_header')
<h1>Editar cliente {{ $distribucion->nomfantasia ?: $distribucion->razonsocial }}</h1>
@endsection

@section('content')
@include('pages.Distribucion.form', ['action' => route('distribucion.update', $distribucion->id), 'method' => 'PUT',
'buttonText' => 'Guardar cambios', 'distribucion' => $distribucion])
@endsection
