<?php

namespace App\Console\Commands;

use App\Services\Auditoria;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Agrega a expedicion_general los certificados de calidad de General.xlsx (hoja Registro) que todavía no están.
 * Recibe la hoja exportada a CSV (columnas: fila, fecha, mo, cl, modo, prod, p, l, pl, w, gh, gs, gi, hum, cz, est, abs, fn, punt, particularidades).
 *
 * - Cruza registro por registro (fecha, molino, cliente, producto y valores) contra lo que ya hay.
 * - Solo agrega los que no están y son posteriores a --desde (lo ya migrado llega hasta el 27/02/2025).
 * - Los que no coinciden y son anteriores NO se cargan: se informan (pueden ser correcciones o bajas del sistema anterior).
 * - Se puede repetir sin duplicar.
 */
class ExpedicionGeneralImportar extends Command
{
  protected $signature = 'expedicion:general {archivo : CSV de la hoja Registro de General.xlsx (fila,fecha,mo,cl,modo,prod,...)} {--desde=2025-02-28 : Solo agrega registros desde esta fecha} {--simular}';

  protected $description = 'Agrega los certificados de calidad de General.xlsx que faltan en expedicion_general';

  /** columna del Excel (0 = A) => campo */
  private const COLUMNAS = [
    2 => 'mo', 3 => 'cl', 5 => 'modo', 6 => 'prod', 7 => 'p', 8 => 'l', 9 => 'pl', 10 => 'w', 11 => 'gh', 12 => 'gs',
    13 => 'gi', 14 => 'hum', 15 => 'cz', 16 => 'est', 17 => 'abs', 18 => 'fn', 19 => 'punt', 20 => 'particularidades',
  ];
  private const ENTEROS = ['p', 'l', 'w', 'gi', 'est', 'abs', 'fn', 'punt'];
  private const DECIMALES = ['pl' => 2, 'gh' => 2, 'gs' => 2, 'hum' => 2, 'cz' => 3];

  public function handle(): int
  {
    $desde = $this->option('desde');
    // CSV extraído de la hoja Registro (fila, fecha, mo, cl, ...): leer el Excel directo con PhpSpreadsheet tarda
    // más de 10 minutos por las 62.000 filas declaradas de la hoja.
    $fh = fopen($this->argument('archivo'), 'r') ?: throw new \RuntimeException('No se puede abrir el archivo');
    $encabezado = fgetcsv($fh);
    // Lo que ya hay, como multiconjunto de claves
    $existentes = [];
    foreach (DB::table('expedicion_general')->get() as $r) {
      $k = $this->clave((array) $r);
      $existentes[$k] = ($existentes[$k] ?? 0) + 1;
    }

    $nuevos = [];
    $anterioresSinCoincidir = [];
    $coinciden = 0;
    $leidos = 0;
    while (($datos = fgetcsv($fh)) !== false) {
      $d = array_combine($encabezado, $datos);
      $fila = (int) $d['fila'];
      $fecha = $d['fecha'];
      $leidos++;
      $r = ['fecha' => $fecha];
      foreach (self::COLUMNAS as $campo) {
        $r[$campo] = $this->valor($campo, $d[$campo] ?? null);
      }
      $k = $this->clave($r);
      if (($existentes[$k] ?? 0) > 0) {
        $existentes[$k]--;
        $coinciden++;
      } elseif ($fecha >= $desde) {
        $nuevos[] = $r + ['status' => 'A', 'origen' => 'planilla', 'revisado' => 1, 'created_at' => now(), 'updated_at' => now()];
      } else {
        $anterioresSinCoincidir[] = ['fila' => $fila] + $r;
      }
    }

    fclose($fh);
    $this->info("Registros en la planilla: $leidos · ya estaban: $coinciden · nuevos desde $desde: " . count($nuevos)
      . ' · anteriores que no coinciden (no se cargan): ' . count($anterioresSinCoincidir)
      . ' · en la app y no en el Excel: ' . array_sum($existentes));
    if ($anterioresSinCoincidir) {
      $this->table(['Fila', 'Fecha', 'Mo', 'Cl', 'Prod', 'P', 'L', 'W'], array_map(fn($r) => [$r['fila'], $r['fecha'], $r['mo'], $r['cl'], $r['prod'], $r['p'], $r['l'], $r['w']], array_slice($anterioresSinCoincidir, 0, 15)));
    }

    if (!$this->option('simular') && $nuevos) {
      DB::transaction(function () use ($nuevos) {
        foreach (array_chunk($nuevos, 500) as $lote) {
          DB::table('expedicion_general')->insert($lote);
        }
      });
      app(Auditoria::class)->manual('alta', 'Expedición', 'expedicion_general', null,
        'Importación desde ' . basename($this->argument('archivo')) . ': ' . count($nuevos) . " certificados desde $desde");
      $this->info('Agregados: ' . count($nuevos));
    }
    return self::SUCCESS;
  }



  private function valor(string $campo, mixed $v): mixed
  {
    if (is_string($v)) {
      $v = trim($v);
    }
    if ($v === null || $v === '') {
      return null;
    }
    if (in_array($campo, self::ENTEROS, true)) {
      return is_numeric($v) ? (int) round((float) $v) : null;
    }
    if (isset(self::DECIMALES[$campo])) {
      return is_numeric($v) ? round((float) $v, self::DECIMALES[$campo]) : null;
    }
    return mb_substr(is_float($v) && floor($v) == $v ? (string) (int) $v : (string) $v, 0, 255);
  }

  /** Clave para comparar un registro del Excel con uno de la base. */
  private function clave(array $r): string
  {
    $partes = [substr((string) $r['fecha'], 0, 10)];
    foreach (['mo', 'cl', 'prod', 'p', 'l', 'w', 'gh', 'hum', 'cz', 'fn'] as $c) {
      $v = $r[$c] ?? null;
      if ($c === 'cz' && is_numeric($v)) {
        $v = round((float) $v, 2);  // la migración anterior guardó la ceniza con 2 decimales (0,659 -> 0,660)
      }
      $partes[] = is_numeric($v) ? rtrim(rtrim(number_format((float) $v, 3, '.', ''), '0'), '.') : mb_strtolower(trim((string) $v));
    }
    return implode('|', $partes);
  }
}
