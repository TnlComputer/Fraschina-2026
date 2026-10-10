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
