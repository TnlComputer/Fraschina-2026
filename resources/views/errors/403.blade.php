@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('title', 'Sin permiso')

@section('auth_header', 'No tiene permiso')

@section('auth_body')
@php
$motivo = $exception->getMessage();
// Mensajes propios en español; los genéricos de los paquetes (en inglés) se reemplazan
$motivo = ($motivo && !preg_match('/[a-z] (does not|not have|unauthorized|forbidden)/i', $motivo)) ? $motivo : 'Su rol no tiene acceso a esta pantalla o acción.';
@endphp
<p class="small text-center">{{ $motivo }}</p>
<p class="small text-center text-muted">Si lo necesita, pídale al administrador que revise su rol.</p>
<a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}" class="btn btn-outline-secondary btn-block">Volver</a>
<a href="{{ route('dashboard') }}" class="btn btn-primary btn-block">Ir al inicio</a>
@endsection
