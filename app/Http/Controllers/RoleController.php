<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Roles: cada rol define a qué secciones entra el usuario y qué puede hacer (alta / editar / eliminar).
 * El acceso de cada usuario lo define solo su rol.
 * Los roles con el permiso Super Usuario (permiso_99) solo los ve y modifica otro Super Usuario.
 */
class RoleController extends Controller
{
  public const SUPER = 'permiso_99';

  /** Permisos de sección (a qué pantallas entra) y de acción (qué puede hacer con los registros). */
  public const SECCIONES = ['permiso_1', 'permiso_2', 'permiso_3', 'permiso_4', 'permiso_5', 'permiso_6', 'permiso_7', 'permiso_8', 'permiso_9'];
  public const ACCIONES = ['permiso_11', 'permiso_12', 'permiso_13'];

  public function index(Request $request)
  {
    $roles = Role::with('permissions')->withCount('users')->orderBy('name')->get()
      ->filter(fn($rol) => $this->puedeVer($request, $rol));
    $permisos = Permission::orderBy('id')->get()->keyBy('name');

    return view('pages.Tools.Role.index', compact('roles', 'permisos'));
  }

  public function create()
  {
    return $this->formulario(new Role());
  }

  public function store(Request $request)
  {
    $datos = $this->validar($request);
    $role = Role::create(['name' => $datos['name'], 'guard_name' => 'web']);
    $role->syncPermissions($this->permisosElegidos($request, $role));

    return redirect()->route('roles.index')->with('success', "Rol «{$role->name}» creado.");
  }

  public function show(Request $request, Role $role)
  {
    return redirect()->route('roles.edit', $role);
  }

  public function edit(Request $request, Role $role)
  {
    abort_unless($this->puedeVer($request, $role), 403, 'Solo el Super Usuario puede modificar este rol.');
    return $this->formulario($role);
  }

  public function update(Request $request, Role $role)
  {
    abort_unless($this->puedeVer($request, $role), 403, 'Solo el Super Usuario puede modificar este rol.');
    $datos = $this->validar($request, $role);
    $role->update(['name' => $datos['name']]);
    $role->syncPermissions($this->permisosElegidos($request, $role));

    return redirect()->route('roles.index')->with('success', "Rol «{$role->name}» actualizado.");
  }

  public function destroy(Request $request, Role $role)
  {
    abort_unless($this->puedeVer($request, $role), 403, 'Solo el Super Usuario puede eliminar este rol.');
    if ($role->checkPermissionTo(self::SUPER)) {
      return back()->with('error', 'El rol del Super Usuario no se puede eliminar.');
    }
    if ($n = $role->users()->count()) {
      return back()->with('error', "No se puede eliminar «{$role->name}»: lo tienen $n usuario(s). Asígneles otro rol primero.");
    }

    $role->delete();  // borrado lógico

    return redirect()->route('roles.index')->with('success', "Rol «{$role->name}» eliminado.");
  }

  private function formulario(Role $role)
  {
    $permisos = Permission::whereIn('name', array_merge(self::SECCIONES, self::ACCIONES))->get()->keyBy('name');

    return view('pages.Tools.Role.form', [
      'role' => $role,
      'secciones' => collect(self::SECCIONES)->map(fn($n) => $permisos[$n] ?? null)->filter(),
      'acciones' => collect(self::ACCIONES)->map(fn($n) => $permisos[$n] ?? null)->filter(),
      'esSuper' => $role->exists && $role->checkPermissionTo(self::SUPER),
    ]);
  }

  private function validar(Request $request, ?Role $role = null): array
  {
    return $request->validate([
      'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($role?->id)->whereNull('deleted_at')],
      'permissions' => 'nullable|array',
      'permissions.*' => 'integer|exists:permissions,id',
    ], [], ['name' => 'nombre del rol']);
  }

  /**
   * Permisos que se guardan: los tildados (nunca permiso_99 desde el formulario) y, si el rol ya era
   * de Super Usuario, se le conserva.
   */
  private function permisosElegidos(Request $request, Role $role): array
  {
    $nombres = Permission::whereIn('id', (array) $request->input('permissions', []))
      ->whereIn('name', array_merge(self::SECCIONES, self::ACCIONES))
      ->pluck('name')->all();
    if ($role->exists && $role->checkPermissionTo(self::SUPER)) {
      $nombres[] = self::SUPER;
    }
    return $nombres;
  }

  private function puedeVer(Request $request, Role $role): bool
  {
    return !$role->checkPermissionTo(self::SUPER) || $request->user()->can(self::SUPER);
  }
}
