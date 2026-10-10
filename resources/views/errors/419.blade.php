@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('title', 'Sesión expirada')

@section('auth_header', 'Su sesión expiró')

@section('auth_body')
<p class="small text-center">Pasó demasiado tiempo sin actividad. Vuelva a ingresar para continuar.</p>
<a href="{{ route('login') }}" class="btn btn-primary btn-block">Ir al ingreso</a>
@endsection
