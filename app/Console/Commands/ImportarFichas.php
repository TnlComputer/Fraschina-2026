<?php

namespace App\Console\Commands;

use App\Services\Auditoria;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as FechaExcel;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Importa las planillas de Fichas.xlsx (una hoja por ficha) a la tabla fichas.
 *
 * - Fila 1: saldo de la hoja (columna K = suma de I), se usa para verificar. Fila 2: encabezados. Desde la 3: movimientos.
 * - Nada se pierde: lo que no se puede convertir (fechas mal cargadas, texto en columnas numéricas) y las
 *   columnas a la derecha de "O" se guardan tal cual en "extra".
 * - Se puede correr de nuevo: las filas ya importadas (misma hoja y fila) no se duplican ni se pisan.
 */
class ImportarFichas extends Command
{
  protected $signature = 'fichas:importar {archivo : Ruta al Excel de fichas} {--simular : Solo verifica, no guarda}';

  protected $description = 'Importa Fichas.xlsx a la tabla fichas (Expedición)';

  /** Columnas A..P del Excel => campo y tipo */
  private const COLUMNAS = [
    'M' => ['m', 'entero'], 'PN' => ['pn', 'entero'], '*' => ['es', 'texto'], 'FE' => ['fe', 'fecha'],
    'OR' => ['ord', 'texto'], 'PL' => ['pl', 'texto'], 'CA' => ['ca', 'texto'], 'T/B' => ['tb', 'texto'],
    'K' => ['k', 'numero'], 'P' => ['p', 'numero'], 'I' => ['i', 'numero'], 'D' => ['d', 'texto'],
    'N' => ['n', 'entero'], 'FP' => ['fp', 'fecha'], 'PP' => ['pp', 'entero'], 'O' => ['o', 'texto'],
  ];

  /** Hojas que no son fichas */
  private const IGNORAR = ['LISTAS'];

  public function handle(): int
  {
    $archivo = $this->argument('archivo');
    if (!is_file($archivo)) {
      $this->error("No existe el archivo $archivo");
      return self::FAILURE;
    }

    $lector = IOFactory::createReaderForFile($archivo);
    $lector->setReadDataOnly(true);
    $libro = $lector->load($archivo);

    $resumen = [];
    $problemas = 0;
    $totalLeidas = 0;
    $totalNuevas = 0;

    foreach ($libro->getWorksheetIterator() as $hoja) {
      $nombre = trim($hoja->getTitle());
      if (in_array(mb_strtoupper($nombre), self::IGNORAR, true)) {
        continue;
      }

      $encabezados = $this->fila($hoja, 2, 16);
      $esperados = array_keys(self::COLUMNAS);
      if (array_map(fn($v) => mb_strtoupper(trim((string) $v)), $encabezados) !== $esperados) {
        $this->warn("Hoja $nombre: encabezados distintos, se omite (" . implode(',', $encabezados) . ')');
        $problemas++;
        continue;
      }

      $saldo = $this->valor($hoja->getCell('K1'));
      $filas = [];
      $sumaI = 0.0;
      $ultimaColumna = Coordinate::columnIndexFromString($hoja->getHighestDataColumn());
      $ultimaFila = $hoja->getHighestDataRow();

      for ($r = 3; $r <= $ultimaFila; $r++) {
        $valores = $this->fila($hoja, $r, $ultimaColumna);
        if (!array_filter($valores, fn($v) => $v !== null && $v !== '')) {
          continue;
        }
        $fila = ['origen_tabla' => $nombre, 'fila' => $r];
        $extra = [];
        foreach ($esperados as $idx => $titulo) {
          [$campo, $tipo] = self::COLUMNAS[$titulo];
          [$convertido, $original] = $this->convertir($valores[$idx] ?? null, $tipo);
          $fila[$campo] = $convertido;
          if ($original !== null) {
            $extra[$titulo] = $original;  // valor que no se pudo convertir, guardado tal cual
          }
        }
        for ($c = 16; $c < count($valores); $c++) {
          if ($valores[$c] !== null && $valores[$c] !== '') {
            $extra['col_' . Coordinate::stringFromColumnIndex($c + 1)] = $this->texto($valores[$c]);
          }
        }
        $fila['extra'] = $extra ? json_encode($extra, JSON_UNESCAPED_UNICODE) : null;
        $fila['created_at'] = $fila['updated_at'] = now();
        if (is_numeric($valores[10] ?? null)) {
          $sumaI += (float) $valores[10];
        }
        $filas[] = $fila;
      }

      $cuadra = !is_numeric($saldo) || abs((float) $saldo - $sumaI) < 0.05;
      if (!$cuadra) {
        $problemas++;
      }

      $nuevas = 0;
      if (!$this->option('simular')) {
        foreach (array_chunk($filas, 500) as $lote) {
          $nuevas += DB::table('fichas')->insertOrIgnore($lote);
        }
      }
      $totalLeidas += count($filas);
      $totalNuevas += $nuevas;
      $resumen[] = [$nombre, count($filas), $nuevas, is_numeric($saldo) ? number_format((float) $saldo, 2, ',', '.') : '—', $cuadra ? 'sí' : 'NO'];
    }

    $this->table(['Hoja', 'Filas', 'Nuevas', 'Saldo (fila 1)', 'Cuadra'], $resumen);
    $this->info("Hojas: " . count($resumen) . " · Filas leídas: $totalLeidas · Nuevas: $totalNuevas" . ($this->option('simular') ? ' (simulación, no se guardó nada)' : ''));

    if (!$this->option('simular') && $totalNuevas) {
      app(Auditoria::class)->manual('alta', 'Expedición', 'fichas', null,
        "Importación de fichas desde " . basename($archivo) . ": $totalNuevas filas en " . count($resumen) . ' hojas');
    }

    if ($problemas) {
      $this->warn("Hojas con observaciones: $problemas");
    }
    return $problemas ? self::FAILURE : self::SUCCESS;
  }

  /** Valores de una fila (con el resultado guardado de las fórmulas, sin recalcular). */
  private function fila(Worksheet $hoja, int $r, int $columnas): array
  {
    $valores = [];
    for ($c = 1; $c <= $columnas; $c++) {
      $valores[] = $this->valor($hoja->getCell([$c, $r]));
    }
    return $valores;
  }

  private function valor($celda): mixed
  {
    $v = $celda->getValue();
    if (is_string($v) && str_starts_with($v, '=')) {
      $v = $celda->getOldCalculatedValue();
    }
    if ($v instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
      $v = $v->getPlainText();
    }
    return is_string($v) ? trim($v) : $v;
  }

  /** @return array{0: mixed, 1: mixed} [valor convertido, valor original si no se pudo convertir] */
  private function convertir(mixed $v, string $tipo): array
  {
    if ($v === null || $v === '') {
      return [null, null];
    }
    switch ($tipo) {
      case 'entero':
        return is_numeric($v) && floor((float) $v) == (float) $v && (float) $v >= 0 ? [(int) $v, null] : [null, $this->texto($v)];
      case 'numero':
        return is_numeric($v) ? [round((float) $v, 3), null] : [null, $this->texto($v)];
      case 'fecha':
        return ($f = $this->fecha($v)) ? [$f, null] : [null, $this->texto($v)];
      default:
        return [mb_substr($this->texto($v), 0, $tipo === 'texto' ? 65000 : 255), null];
    }
  }

  /** AAAAMMDD (como lo cargan las planillas) a fecha; null si no es una fecha válida. */
  private function fecha(mixed $v): ?string
  {
    $s = is_numeric($v) ? (string) (int) $v : (string) $v;
    if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $s, $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1]) && $m[1] >= 1990 && $m[1] <= 2100) {
      return "{$m[1]}-{$m[2]}-{$m[3]}";
    }
    return null;
  }

  private function texto(mixed $v): string
  {
    if ($v instanceof \DateTimeInterface) {
      return Carbon::instance($v)->format('Y-m-d');
    }
    if (is_float($v) && floor($v) == $v && abs($v) < 1e15) {
      return (string) (int) $v;
    }
    return trim((string) $v);
  }
}
