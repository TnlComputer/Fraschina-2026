@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('auth_header', 'Verifique su correo electrónico')

@section('auth_body')
@if (session('status') == 'verification-link-sent')
<div class="alert alert-success small">Le enviamos un nuevo enlace de verificación a su correo.</div>
@endif
<p class="small">Antes de continuar, revise su correo y haga clic en el enlace de verificación que le enviamos.</p>
<form method="POST" action="{{ route('verification.send') }}" class="mb-2">
  @csrf
  <button type="submit" class="btn btn-primary btn-block">Reenviar enlace de verificación</button>
</form>
<form method="POST" action="{{ route('logout') }}">
  @csrf
  <button type="submit" class="btn btn-link btn-block">Salir</button>
</form>
@endsection
