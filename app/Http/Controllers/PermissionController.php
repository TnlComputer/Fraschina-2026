<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

/**
 * Permisos: los define el sistema (las rutas y pantallas usan su código, ej. permiso_2), por eso no se
 * crean, renombran ni eliminan desde acá: solo se cambia el nombre que se muestra.
 * Qué permisos tiene cada usuario se decide en Roles.
 */
class PermissionController extends Controller
{
  public function index()
  {
    $permisos = Permission::with(['roles' => fn($q) => $q->withCount('users')->orderBy('name')])->get()->keyBy('name');

    $grupos = [
      'Secciones' => ['A qué pantallas entra', RoleController::SECCIONES],
      'Acciones' => ['Qué puede hacer con los registros', RoleController::ACCIONES],
      'Reservados' => ['Solo los asigna el Super Usuario', RoleController::RESERVADOS],
    ];
    $grupos = collect($grupos)->map(fn($g) => [$g[0], collect($g[1])->map(fn($n) => $permisos[$n] ?? null)->filter()]);

    return view('pages.Tools.Permiso.index', compact('grupos'));
  }

  public function edit(Permission $permission)
  {
    return view('pages.Tools.Permiso.form', compact('permission'));
  }

  public function update(Request $request, Permission $permission)
  {
    $datos = $request->validate(['description' => 'required|string|max:100'], [], ['description' => 'nombre']);
    $permission->update(['description' => $datos['description']]);

    return redirect()->route('permissions.index')->with('success', "Permiso «{$permission->description}» actualizado.");
  }
}
