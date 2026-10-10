<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controla las acciones sobre los registros además del permiso de la sección:
 *   permiso_11 Alta registros     → create / store
 *   permiso_12 Editar registros   → edit / update y otras acciones que modifican (POST / PUT / PATCH)
 *   permiso_13 Eliminar registros → destroy / DELETE
 * Ver y exportar no piden nada extra. El Super Usuario (permiso_99) pasa siempre (Gate::before).
 */
class PermisoAccion
{
  public const ALTA = 'permiso_11';
  public const EDITAR = 'permiso_12';
  public const ELIMINAR = 'permiso_13';

  /** Rutas que envían datos pero no modifican registros. */
  private const SOLO_LECTURA = ['export.generate'];

  public function handle(Request $request, Closure $next): Response
  {
    $permiso = $this->permisoNecesario($request);

    if ($permiso && !$request->user()?->can($permiso)) {
      $accion = [self::ALTA => 'dar de alta', self::EDITAR => 'modificar', self::ELIMINAR => 'eliminar'][$permiso];
      abort(403, "Su rol no tiene permiso para $accion registros.");
    }

    return $next($request);
  }

  public function permisoNecesario(Request $request): ?string
  {
    $route = $request->route();
    if (in_array($route?->getName(), self::SOLO_LECTURA, true)) {
      return null;
    }

    return match ($route?->getActionMethod()) {
      'create', 'store' => self::ALTA,
      'edit', 'update' => self::EDITAR,
      'destroy' => self::ELIMINAR,
      default => match ($request->method()) {
        'DELETE' => self::ELIMINAR,
        'POST', 'PUT', 'PATCH' => self::EDITAR,
        default => null,
      },
    };
  }
}
