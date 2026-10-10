<?php

namespace App\Services;

use App\Models\Auditoria as Registro;
use App\Models\ExpPedidoMovimiento;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Log de operaciones. Se alimenta de los eventos de Eloquent (created, updated, deleted,
 * restored) de todos los modelos, así no depende de cada pantalla.
 */
class Auditoria
{
  /** Modelos que no se auditan (el propio log y otros historiales). */
  private const EXCLUIDOS = [Registro::class, ExpPedidoMovimiento::class];

  /** Campos que nunca se guardan en el log. */
  private const SENSIBLES = ['password', 'remember_token'];

  /** Campos que no cuentan como modificación (remember_token cambia en cada ingreso/salida). */
  private const IGNORADOS = ['created_at', 'updated_at', 'remember_token'];

  /** Sector según el prefijo de la tabla (el primero que coincide). */
  private const SECTORES = [
    'distribucion' => 'Distribución',
    'exp_' => 'Expedición',
    'expedicion' => 'Expedición',
    'fichas' => 'Expedición',
    'molino' => 'Molinos',
    'proveedor' => 'Proveedores',
    'representacion' => 'Representación',
    'agro' => 'Agro',
    'transporte' => 'Transporte',
    'agendas' => 'Agenda General',
    'users' => 'Usuarios y permisos',
    'roles' => 'Usuarios y permisos',
    'permissions' => 'Usuarios y permisos',
    'aux' => 'Tablas auxiliares',
    'configuraciones' => 'Configuración',
    'productos_c_d_a' => 'Tablas auxiliares',
  ];

  /** Atributos que sirven para describir el registro, en orden de preferencia. */
  private const NOMBRES = ['razonsocial', 'nomfantasia', 'name', 'nombre', 'sigla', 'calidad', 'productoCDA', 'tarea', 'destino', 'calle', 'email'];

  public static function sectores(): array
  {
    return array_values(array_unique(array_merge(['Accesos'], array_values(self::SECTORES), ['Otros'])));
  }

  public function modelo(Model $modelo, string $evento): void
  {
    if (in_array(get_class($modelo), self::EXCLUIDOS, true)) {
      return;
    }

    $accion = match ($evento) {
      'created' => 'alta',
      'deleted' => 'eliminacion',
      'restored' => 'restauracion',
      default => 'modificacion',
    };

    $cambios = match ($evento) {
      'updated' => $this->diferencias($modelo),
      'created' => $this->valores($modelo->getAttributes(), nuevos: true),
      default => $this->valores($modelo->getAttributes(), nuevos: false),
    };

    if ($evento === 'updated') {
      // Sin cambios reales, o solo deleted_at (la restauración ya se registra con su propio evento)
      if (!$cambios || array_keys($cambios) === ['deleted_at']) {
        return;
      }
      $accion = $this->accionPorEstado($cambios) ?? 'modificacion';
    }

    $this->guardar(
      accion: $accion,
      sector: $this->sector($modelo->getTable()),
      tabla: $modelo->getTable(),
      registroId: (string) $modelo->getKey(),
      descripcion: $this->describir($modelo),
      cambios: $cambios,
    );
  }

  /**
   * Para operaciones que no pasan por un modelo (ej. asignación de roles).
   */
  public function manual(string $accion, string $sector, string $tabla, ?string $registroId, string $descripcion, array $cambios = []): void
  {
    $this->guardar($accion, $sector, $tabla, $registroId, $descripcion, $cambios);
  }

  /**
   * Ingresos, salidas e intentos fallidos. Para un intento fallido se guarda el mail usado, nunca la contraseña.
   */
  public function acceso(string $accion, ?Authenticatable $usuario, string $descripcion, array $cambios = []): void
  {
    try {
      $this->guardar($accion, 'Accesos', 'users', $usuario ? (string) $usuario->getAuthIdentifier() : null, $descripcion, $cambios, $usuario);
    } catch (\Throwable $e) {
      // Un problema con el log nunca debe impedir ingresar o salir
      report($e);
    }
  }

  private function guardar(string $accion, string $sector, string $tabla, ?string $registroId, string $descripcion, array $cambios, ?Authenticatable $usuario = null): void
  {
    $usuario ??= Auth::user();
    $request = app()->runningInConsole() ? null : request();

    Registro::create([
      'user_id' => $usuario?->getAuthIdentifier(),
      'usuario' => $usuario?->name ?? (app()->runningInConsole() ? 'Sistema (tarea programada)' : null),
      'accion' => $accion,
      'sector' => $sector,
      'tabla' => $tabla,
      'registro_id' => $registroId,
      'descripcion' => Str::limit($descripcion, 250),
      'cambios' => $cambios ?: null,
      'ip' => $request?->ip(),
      'url' => $request ? Str::limit($request->method() . ' ' . $request->path(), 250) : null,
    ]);
  }

  /** [campo => [antes, después]] de los campos que cambiaron. */
  private function diferencias(Model $modelo): array
  {
    $cambios = [];
    foreach ($modelo->getChanges() as $campo => $nuevo) {
      if (in_array($campo, self::IGNORADOS, true)) {
        continue;
      }
      $anterior = $modelo->getOriginal($campo);
      if (in_array($campo, self::SENSIBLES, true)) {
        $cambios[$campo] = ['***', '***'];
        continue;
      }
      if ((string) $this->escalar($anterior) === (string) $this->escalar($nuevo)) {
        continue;
      }
      $cambios[$campo] = [$this->escalar($anterior), $this->escalar($nuevo)];
    }
    return $cambios;
  }

  /** Valores de un alta (null → valor) o de una baja (valor → null). */
  private function valores(array $atributos, bool $nuevos): array
  {
    $salida = [];
    foreach ($atributos as $campo => $valor) {
      if ($valor === null || $valor === '' || in_array($campo, self::IGNORADOS, true) || in_array($campo, self::SENSIBLES, true)) {
        continue;
      }
      $salida[$campo] = $nuevos ? [null, $this->escalar($valor)] : [$this->escalar($valor), null];
    }
    return $salida;
  }

  /**
   * Las bajas de la app se hacen con status 'D', is_active = 0 o deleted_at: se registran como eliminación.
   */
  private function accionPorEstado(array $cambios): ?string
  {
    foreach (['status', 'is_active', 'activo', 'deleted_at'] as $campo) {
      if (!array_key_exists($campo, $cambios)) {
        continue;
      }
      [$antes, $despues] = $cambios[$campo];
      $inactivo = fn($v) => in_array((string) $v, ['D', '0', 'false', ''], true) || $v === null;
      if ($campo === 'deleted_at') {
        return $despues ? 'eliminacion' : 'restauracion';
      }
      if (!$inactivo($antes) && $inactivo($despues)) {
        return 'eliminacion';
      }
      if ($inactivo($antes) && !$inactivo($despues)) {
        return 'restauracion';
      }
    }
    return null;
  }

  private function sector(string $tabla): string
  {
    foreach (self::SECTORES as $prefijo => $sector) {
      if (str_starts_with($tabla, $prefijo)) {
        return $sector;
      }
    }
    return 'Otros';
  }

  private function describir(Model $modelo): string
  {
    $nombre = class_basename($modelo) . ' #' . $modelo->getKey();
    foreach (self::NOMBRES as $atributo) {
      $valor = $modelo->getAttribute($atributo);
      if (is_scalar($valor) && trim((string) $valor) !== '') {
        $extra = $atributo === 'nombre' && $modelo->getAttribute('apellido') ? ' ' . $modelo->getAttribute('apellido') : '';
        return $nombre . ' - ' . trim($valor . $extra);
      }
    }
    return $nombre;
  }

  private function escalar(mixed $valor): mixed
  {
    if ($valor instanceof \DateTimeInterface) {
      return $valor->format('Y-m-d H:i:s');
    }
    if (is_array($valor) || is_object($valor)) {
      return json_encode($valor, JSON_UNESCAPED_UNICODE);
    }
    if (is_bool($valor)) {
      return $valor ? 1 : 0;
    }
    return $valor;
  }
}
