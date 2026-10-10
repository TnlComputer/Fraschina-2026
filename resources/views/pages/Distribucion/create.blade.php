@extends('adminlte::page')

@section('title', 'Nuevo cliente')

@section('content_header')
<h1>Nuevo cliente</h1>
@endsection

@section('content')
@include('pages.Distribucion.form', ['action' => route('distribucion.store'), 'method' => 'POST', 'buttonText' => 'Guardar cliente'])
@endsection
