@extends('adminlte::page')

@section('content')
<div class="container">
  <div class="row">
    <!-- Cambio de Nombre -->
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">{{ __('Profile Information') }}</h3>
        </div>
        <div class="card-body">
          <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
          </p>

          <!-- Formulario de Nombre -->
          <form method="post" action="{{ route('profile.update') }}" class="mt-4 space-y-6">
            @csrf
            @method('patch')

            <div class="form-group">
              <label for="name" class="form-label">{{ __('Name') }}</label>
              <input id="name" name="name" type="text" class="form-control mt-1 block w-full"
                value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
              @error('name')
              <div class="text-danger mt-2">{{ $message }}</div>
              @enderror
            </div>

            <div class="d-flex justify-content-between">
              <button type="submit" class="btn btn-primary">{{ __('Save Name') }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Cambio de Correo Electrónico -->
  <div class="row mt-4">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">{{ __('Email Address') }}</h3>
        </div>
        <div class="card-body">
          <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ __('Update your email address and verify it.') }}
          </p>

          <form method="post" action="{{ route('profile.updateEmail') }}" class="mt-4 space-y-6">
            @csrf
            @method('patch')

            <div class="form-group">
              <label for="email" class="form-label">{{ __('Email') }}</label>
              <input id="email" name="email" type="email" class="form-control mt-1 block w-full"
                value="{{ old('email', $user->email) }}" required autocomplete="username" />
              @error('email')
              <div class="text-danger mt-2">{{ $message }}</div>
              @enderror
            </div>

            <div class="d-flex justify-content-between">
              <button type="submit" class="btn btn-primary">{{ __('Save Email') }}</button>
            </div>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
            <div class="mt-4">
              <p class="text-sm text-gray-800 dark:text-gray-200">
                {{ __('Your email address is unverified.') }}

                <button form="send-verification"
                  class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                  {{ __('Click here to re-send the verification email.') }}
                </button>
              </p>
            </div>
            @endif
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Cambio de Contraseña -->
  <div class="row mt-4">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">{{ __('Change Password') }}</h3>
        </div>
        <div class="card-body">
          <form method="post" action="{{ route('profile.updatePassword') }}" class="mt-4 space-y-6">
            @csrf
            @method('patch')

            <div class="form-group">
              <label for="current_password" class="form-label">{{ __('Current Password') }}</label>
              <input id="current_password" name="current_password" type="password"
                class="form-control mt-1 block w-full" required autocomplete="current-password" />
              @error('current_password')
              <div class="text-danger mt-2">{{ $message }}</div>
              @enderror
            </div>

            <div class="form-group">
              <label for="password" class="form-label">{{ __('New Password') }}</label>
              <input id="password" name="password" type="password" class="form-control mt-1 block w-full" required
                autocomplete="new-password" />
              @error('password')
              <div class="text-danger mt-2">{{ $message }}</div>
              @enderror
            </div>

            <div class="form-group">
              <label for="password_confirmation" class="form-label">{{ __('Confirm New Password') }}</label>
              <input id="password_confirmation" name="password_confirmation" type="password"
                class="form-control mt-1 block w-full" required autocomplete="new-password" />
              @error('password_confirmation')
              <div class="text-danger mt-2">{{ $message }}</div>
              @enderror
            </div>

            <div class="d-flex justify-content-between">
              <button type="submit" class="btn btn-primary">{{ __('Save Password') }}</button>
            </div>

            @if (session('status') === 'password-updated')
            <p class="text-sm text-gray-600 dark:text-gray-400">
              {{ __('Password updated successfully.') }}
            </p>
            @endif
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@include('partials.ver-clave')
