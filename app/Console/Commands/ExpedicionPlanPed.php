<?php

namespace App\Console\Commands;

use App\Models\ExpedicionCliente;
use Illuminate\Console\Command;

/**
 * Carga los textos fijos de la planilla de pedido de cada cliente desde Plan ped.xlsx.
 *
 * Recibe el JSON que arma database/scripts/planped_a_json.py (una entrada por hoja: renglones ya unidos en
 * hasta 6 de 200 caracteres, notas por molino y otras columnas tal cual).
 * - Los renglones van a linea1..linea6 del cliente, solo si el cliente no tiene texto cargado (no se pisa nada).
 * - Las notas por molino y el resto de las columnas se agregan a las observaciones del cliente, para revisar
 *   y armar desde "Textos cliente-molino" la planilla de cada molino (decisión del 12/10/2026).
 * - Se puede repetir sin duplicar.
 */
class ExpedicionPlanPed extends Command
{
  protected $signature = 'expedicion:planped {json : Salida de planped_a_json.py} {--simular}';

  protected $description = 'Carga los textos de las planillas de pedido por cliente (Plan ped.xlsx)';

  /** hoja => número de cliente de las planillas (confirmado el 12/10/2026). "Que Rico" queda afuera hasta confirmar. */
  private const HOJAS = [
    'C.Sab.' => 60, 'Cooperativa' => 20, 'Croni' => 29, 'El Sol de BV.' => 45, 'Edra' => 33, 'Goloalfa' => 7,
    'G. Torinese' => 6, 'Hojalmar' => 8, 'Kiskali Tostada' => 32, 'Malsu' => 59, 'Mauri' => 19, 'P. Trio' => 14,
    'P- Orali' => 36, 'Par Nor' => 11, 'Pans' => 13, 'Ricotona' => 50, 'Soriano' => 43, 'Servicorr' => 30,
    'Saborisimo' => 16, 'Toray' => 18, 'Ven Hor' => 28, 'V. Dagri' => 40,
  ];

  /** Hojas que no son el texto principal: se guardan completas en las observaciones del cliente */
  private const SOLO_OBSERVACIONES = ['Kiskali Grissin' => 32];

  public function handle(): int
  {
    $hojas = json_decode(file_get_contents($this->argument('json')), true) ?: throw new \RuntimeException('JSON inválido');
    $resumen = [];

    foreach (self::HOJAS + self::SOLO_OBSERVACIONES as $hoja => $numero) {
      $datos = $hojas[$hoja] ?? null;
      $cliente = ExpedicionCliente::where('numero', $numero)->first();
      if (!$datos || !$cliente) {
        $resumen[] = [$hoja, $numero, '—', $datos ? 'no está el cliente' : 'no está la hoja'];
        continue;
      }

      $textos = 'sin cambios';
      if (!isset(self::SOLO_OBSERVACIONES[$hoja])) {
        if ($cliente->lineasTexto()) {
          $textos = 'ya tenía texto: no se pisa';
        } else {
          foreach (array_slice($datos['lineas'], 0, 6) as $i => $linea) {
            $cliente->{'linea' . ($i + 1)} = mb_substr($linea, 0, 200);
          }
          $textos = count($datos['lineas']) . ' renglones';
        }
      }

      // Observaciones: notas por molino, otras columnas y (para las hojas secundarias) el texto completo
      $marca = "[Plan ped: {$hoja}]";
      $bloque = [];
      if (isset(self::SOLO_OBSERVACIONES[$hoja])) {
        $bloque[] = 'Texto de la planilla: ' . implode(' / ', $datos['lineas']);
      }
      if ($datos['notas_derecha']) {
        $bloque[] = 'Notas por molino: ' . implode(' / ', $datos['notas_derecha']);
      }
      if ($datos['otras_columnas']) {
        $bloque[] = 'Otras columnas: ' . implode(' / ', $datos['otras_columnas']);
      }
      $obs = 'sin notas';
      if ($bloque) {
        if (str_contains((string) $cliente->obs, $marca)) {
          $obs = 'ya estaban';
        } else {
          $cliente->obs = trim(($cliente->obs ? $cliente->obs . "\n\n" : '') . $marca . "\n" . implode("\n", $bloque));
          $obs = 'agregadas';
        }
      }

      if (!$this->option('simular') && $cliente->isDirty()) {
        $cliente->save();   // por Eloquent: queda en la auditoría
      }
      $resumen[] = [$hoja, $numero, $textos, $obs];
    }

    $this->table(['Hoja', 'Cliente', 'Texto de la planilla', 'Observaciones'], $resumen);
    if ($this->option('simular')) {
      $this->info('Simulación: no se guardó nada.');
    }
    return self::SUCCESS;
  }
}
