{{-- Nueva contraseña desde el enlace recibido por mail (ruta password.store) --}}
@php($token = $request->route('token'))

@extends('adminlte::auth.passwords.reset')

@section('password_reset_url', 'reset-password')

@include('partials.ver-clave')
