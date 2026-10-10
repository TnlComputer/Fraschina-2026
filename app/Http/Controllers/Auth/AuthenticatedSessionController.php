<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Models\Role;

class AuthenticatedSessionController extends Controller
{
  /**
   * Display the login view.
   */
  public function create(): View
  {
    return view('auth.login');
  }

  /**
   * Handle an incoming authentication request.
   */
  public function store(LoginRequest $request): RedirectResponse
  {
    $request->authenticate();

    // Validar que el usuario esté activo
    $user = Auth::user();
    if (!$user->is_active) {
      Auth::logout(); // Cerrar cualquier sesión activa
      throw ValidationException::withMessages([
        'email' => __('Tu cuenta está desactivada. Contacta al administrador.'),
      ]);
    }

    // Asignar roles y permisos basados en los roles y permisos existentes en la base de datos
    $this->assignRoleAndPermissions($user);

    $request->session()->regenerate();

    return redirect()->intended(route('dashboard', absolute: false));
  }

  /**
   * Asignar roles y permisos basados en el permission_level del usuario.
   */
  protected function assignRoleAndPermissions($user)
  {
    // Los roles se administran desde Tools > Usuarios. El permission_level es un
    // mecanismo heredado: solo se usa si el usuario aún no tiene rol y la columna existe.
    if ($user->roles()->exists() || is_null($user->permission_level)) {
      return;
    }

    if (!Schema::hasColumn('roles', 'permission_level')) {
      return;
    }

    $role = $this->getRoleForPermissionLevel($user->permission_level);

    if ($role) {
      $user->assignRole($role);
    }
  }

  protected function getRoleForPermissionLevel(int $permissionLevel): ?string
  {
    // Consultamos el rol en la base de datos basándonos en el permission_level
    // Asegúrate de que 'permission_level' está en la tabla de roles
    $role = Role::where('permission_level', $permissionLevel)->first();

    return $role ? $role->name : null; // Retorna el nombre del rol si existe
  }

  /**
   * Destroy an authenticated session.
   */
  public function destroy(Request $request): RedirectResponse
  {
    Auth::guard('web')->logout();

    $request->session()->invalidate();

    $request->session()->regenerateToken();

    return redirect('/');
  }
}
