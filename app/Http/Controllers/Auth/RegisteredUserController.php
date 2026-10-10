<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Models\Role;
use App\Models\Permission;

class RegisteredUserController extends Controller
{
  /**
   * Muestra la vista de registro.
   */
  public function create(): View
  {
    $roles = Role::all(); // Obtener todos los roles
    return view('auth.register', compact('roles'));
  }

  /**
   * Maneja una solicitud de registro entrante.
   */
  public function store(Request $request): RedirectResponse
  {
    $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
      'password' => ['required', 'confirmed', Rules\Password::defaults()],
      'roles' => ['nullable', 'array'], // Permitir que se asignen roles
      'roles.*' => ['exists:roles,name'], // Validar que los roles existan en la tabla roles
      'is_active' => ['nullable', 'boolean'], // Validar el campo is_active como booleano
    ]);

    // Crear el usuario
    $user = User::create([
      'name' => $request->name,
      'email' => $request->email,
      'password' => Hash::make($request->password),
      'is_active' => $request->is_active ?? 1, // Valor predeterminado activo
    ]);

    // Asignar roles al usuario si están presentes
    if ($request->has('roles')) {
      $user->assignRole($request->roles);
    }

    // Emitir evento de registro y autenticar usuario
    event(new Registered($user));
    Auth::login($user);

    return redirect(route('dashboard'))->with('success', 'Usuario registrado correctamente.');
  }
}