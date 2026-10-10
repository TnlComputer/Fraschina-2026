{{-- Ingreso con el mismo estilo que el resto de la app (AdminLTE) --}}
@extends('adminlte::auth.login')

@section('password_reset_url', 'forgot-password')

@section('auth_header')
@if (session('status'))
<div class="alert alert-success small mb-3">{{ session('status') }}</div>
@endif
Ingrese con su correo y contraseña
@endsection

@include('partials.ver-clave')
