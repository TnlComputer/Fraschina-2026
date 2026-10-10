<?php

namespace App\Services;

use App\Models\Configuracion;
use Illuminate\Support\Facades\Mail;

/**
 * Parámetros editables desde Herramientas → Configuración.
 *
 * Cada clave es también la clave de config() que reemplaza (ej. 'expedicion.consulta_manana'), así el
 * código que ya usa config(...) toma el valor guardado sin cambios. Sin valor guardado rige el
 * del archivo de configuración / .env.
 * Lo técnico o secreto (servidor de correo, contraseñas, base de datos) queda en el .env.
 */
class Configuraciones
{
  public const NO_ENVIAR = 'no_enviar';
  public const PRUEBA = 'prueba';
  public const NORMAL = 'normal';

  public const MODOS_CORREO = [
    self::NO_ENVIAR => 'No enviar (solo se registran en el log)',
    self::PRUEBA => 'Enviar todo a una casilla de prueba',
    self::NORMAL => 'Enviar normalmente a los destinatarios',
  ];

  public const DIAS = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 0 => 'Domingo'];

  /** clave => tipo (texto | entero | booleano | lista) */
  public const CLAVES = [
    'mail.modo' => 'texto',
    'mail.casilla_prueba' => 'texto',
    'expedicion.mail_remitente' => 'texto',
    'expedicion.mail_remitente_nombre' => 'texto',
    'expedicion.consultas_activas' => 'booleano',
    'expedicion.consulta_manana' => 'texto',
    'expedicion.consulta_tarde' => 'texto',
    'expedicion.consulta_dias' => 'lista',
    'expedicion.numero_inicial' => 'entero',
    'expedicion.nombre_planilla' => 'texto',
    'expedicion.respaldos_dias' => 'entero',
  ];

  /** Valores por defecto de las claves que no están en config/*.php */
  private const DEFECTOS = [
    'mail.modo' => self::NO_ENVIAR,   // seguro: hasta que alguien lo cambie, no sale ningún mail
    'mail.casilla_prueba' => null,
    'expedicion.consultas_activas' => true,
    'expedicion.consulta_dias' => [1, 2, 3, 4, 5, 6],
  ];

  /**
   * Carga los valores guardados en config() y aplica el modo de envío de correos.
   * Se llama al arrancar la app; si la tabla todavía no existe (instalación, tests) usa los defectos.
   */
  public function aplicar(): void
  {
    try {
      $guardados = Configuracion::pluck('valor', 'clave')->all();
    } catch (\Throwable $e) {
      $guardados = [];
    }

    foreach (self::CLAVES as $clave => $tipo) {
      $valor = array_key_exists($clave, $guardados) ? $this->convertir($guardados[$clave], $tipo) : (config($clave) ?? self::DEFECTOS[$clave] ?? null);
      config([$clave => $valor]);
    }

    $this->aplicarModoCorreo();
  }

  public function valor(string $clave): mixed
  {
    return config($clave);
  }

  /**
   * Guarda los valores que cambiaron (por Eloquent: cada cambio queda en la auditoría).
   */
  public function guardar(array $valores): array
  {
    $cambiadas = [];
    foreach ($valores as $clave => $valor) {
      if (!array_key_exists($clave, self::CLAVES)) {
        continue;
      }
      $texto = $this->aTexto($valor, self::CLAVES[$clave]);
      if ($texto === $this->aTexto(config($clave), self::CLAVES[$clave])) {
        continue;
      }
      Configuracion::updateOrCreate(['clave' => $clave], ['valor' => $texto]);
      config([$clave => $this->convertir($texto, self::CLAVES[$clave])]);
      $cambiadas[] = $clave;
    }
    return $cambiadas;
  }

  private function aplicarModoCorreo(): void
  {
    // Lo que dice el .env (para mostrarlo en la pantalla aunque el modo lo reemplace)
    config(['mail.mailer_env' => config('mail.mailer_env') ?? config('mail.default')]);
    $modo = config('mail.modo');
    $casilla = config('mail.casilla_prueba');

    if ($modo === self::NORMAL) {
      return;
    }
    if ($modo === self::PRUEBA && $casilla) {
      // Los mails salen de verdad, pero todos llegan a la casilla de prueba
      Mail::alwaysTo($casilla);
      return;
    }
    // No enviar (o prueba sin casilla): solo se registran en el log
    config(['mail.default' => 'log']);
  }

  private function convertir(?string $valor, string $tipo): mixed
  {
    return match ($tipo) {
      'entero' => (int) $valor,
      'booleano' => in_array($valor, ['1', 'true', 'SI'], true),
      'lista' => $valor === null || $valor === '' ? [] : array_map('intval', explode(',', $valor)),
      default => $valor === '' ? null : $valor,
    };
  }

  private function aTexto(mixed $valor, string $tipo): ?string
  {
    return match ($tipo) {
      'booleano' => $valor ? '1' : '0',
      'lista' => implode(',', array_map('intval', (array) $valor)),
      'entero' => (string) (int) $valor,
      default => $valor === null || $valor === '' ? null : trim((string) $valor),
    };
  }
}
