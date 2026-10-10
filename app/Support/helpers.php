<?php

/*
 * Funciones de formato para las vistas (formato argentino).
 * Se cargan automáticamente desde composer.json ("autoload" → "files").
 */

use Carbon\Carbon;

if (!function_exists('fecha')) {
  /**
   * Fecha formateada; vacía, nula o 0000-00-00 → $vacio (antes Carbon::parse(null) mostraba la fecha de hoy).
   */
  function fecha($valor, string $formato = 'd/m/Y', string $vacio = '—'): string
  {
    if ($valor === null || $valor === '' || str_starts_with((string) $valor, '0000-00-00')) {
      return $vacio;
    }
    try {
      return ($valor instanceof \DateTimeInterface ? Carbon::instance($valor) : Carbon::parse($valor))->format($formato);
    } catch (\Throwable $e) {
      return (string) $valor;
    }
  }
}

if (!function_exists('hora')) {
  /**
   * Hora HH:MM; vacía o 00:00:00 → ''.
   */
  function hora($valor): string
  {
    $valor = trim((string) $valor);
    return ($valor === '' || $valor === '00:00:00' || $valor === '00:00') ? '' : substr($valor, 0, 5);
  }
}

if (!function_exists('horario')) {
  /**
   * Rango horario "08:00 a 12:00"; sin datos → $vacio.
   */
  function horario($desde, $hasta, string $vacio = '—'): string
  {
    $d = hora($desde);
    $h = hora($hasta);
    if ($d === '' && $h === '') {
      return $vacio;
    }
    return trim($d . ($h !== '' ? ' a ' . $h : ''));
  }
}

if (!function_exists('color_css')) {
  /**
   * Color CSS a partir de los nombres que usan las tablas auxiliares ("Rojo", "Verde"...).
   * Devuelve tonos suaves, legibles como fondo. Un valor ya válido (#hex) se respeta.
   */
  function color_css($nombre, ?string $porDefecto = null): ?string
  {
    $nombre = trim((string) $nombre);
    if (preg_match('/^#[0-9a-f]{3,8}$/i', $nombre)) {
      return $nombre;
    }
    $colores = [
      'rojo' => '#f8d7da', 'verde' => '#d4edda', 'amarillo' => '#fff3cd', 'naranja' => '#ffe0c2',
      'azul' => '#cfe2ff', 'celeste' => '#d1ecf1', 'violeta' => '#e2d9f3', 'gris' => '#e2e3e5',
      'rosa' => '#f9d6e8', 'marron' => '#e8d9c5', 'marrón' => '#e8d9c5', 'negro' => '#d6d8db',
    ];
    return $colores[mb_strtolower($nombre)] ?? $porDefecto;
  }
}

if (!function_exists('numero')) {
  /**
   * Número con formato argentino: 1.234,56
   */
  function numero($valor, int $decimales = 0): string
  {
    return number_format((float) $valor, $decimales, ',', '.');
  }
}

if (!function_exists('pesos')) {
  /**
   * Importe en pesos con formato argentino: $ 1.234,56
   */
  function pesos($valor, int $decimales = 2): string
  {
    return '$ ' . numero($valor, $decimales);
  }
}
