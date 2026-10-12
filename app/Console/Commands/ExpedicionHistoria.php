<?php

namespace App\Console\Commands;

use App\Services\Auditoria;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Carga el historial de Diaria y Ctrol (CSV de database/scripts/historia_a_csv.py) en exp_historial_diaria y
 * exp_historial_control. Nada se interpreta salvo fecha, molino y cliente; el resto queda como texto.
 * Se puede repetir: las filas ya cargadas (misma fila de la planilla) no se duplican ni se pisan.
 */
class ExpedicionHistoria extends Command
{
  protected $signature = 'expedicion:historia {diaria : diaria.csv} {ctrol : ctrol.csv} {--simular}';

  protected $description = 'Carga el historial de Diaria y Ctrol de las planillas de Expedición';

  public function handle(): int
  {
    $diaria = $this->leer($this->argument('diaria'), function (array $d) {
      $original = [];
      return [
        'fila' => (int) $d['fila'],
        'fecha' => $this->fecha($d['f'], 'F', $original),
        'hora' => $this->corto($d['h'], 10),
        'molino_codigo' => $this->numero($d['m']),
        'pedido' => $this->corto($d['p'], 12),
        'ra' => $this->corto($d['ra'], 12),
        'cliente_numero' => $this->numero($d['c']),
        'marca' => $this->corto($d['es'], 5),
        'fecha_entrega' => $this->fecha($d['fe'], 'FE', $original),
        'hora_entrega' => $this->corto($d['he'], 10),
        'orden' => $this->corto($d['ord'], 10),
        'planta' => $this->corto($d['pl'], 5),
        'calidad' => $this->corto($d['ca'], 10),
        'tolvas' => $this->corto($d['t'], 10),
        'bolsas' => $this->corto($d['b'], 10),
        'precio' => $this->corto($d['precio'], 15),
        'accion' => $this->corto($d['d'], 10),
        'observaciones' => $d['o'] !== '' ? $d['o'] : null,
        'original' => $original ? json_encode($original, JSON_UNESCAPED_UNICODE) : null,
      ];
    });

    $control = $this->leer($this->argument('ctrol'), function (array $d) {
      $original = [];
      $codigo = $this->numero($d['m']);
      if ($d['m'] !== '' && !$codigo) {
        $original['M'] = $d['m'];   // ej. "ALBERTI" escrito en lugar del código
      }
      return [
        'fila' => (int) $d['fila'],
        'fecha' => $this->fecha($d['fe'], 'FE', $original),
        'molino_codigo' => $codigo,
        'cliente_numero' => preg_match('/^\*?\s*(\d{1,2})(?!\d)/', $d['c'], $m) ? (int) $m[1] : null,
        'detalle' => $this->corto($d['c'], 100),
        'transporte' => $this->corto($d['transporte'], 100),
        'hora_carga' => $this->corto($d['hc'], 255),
        'estado' => $this->corto($d['o'], 255),
        'confirmado' => $this->corto($d['g'], 10),
        'kg_molino' => $this->corto($d['kg_mol'], 100),
        'kg_cliente' => $this->corto($d['kg_cte'], 150),
        'kg_diferencia' => $this->corto($d['kg_dif'], 100),
        'remito' => $this->corto($d['remito'], 50),
        'otro' => $this->corto($d['cte'], 50),
        'original' => $original ? json_encode($original, JSON_UNESCAPED_UNICODE) : null,
      ];
    });

    $nuevasD = $nuevasC = 0;
    if (!$this->option('simular')) {
      foreach (array_chunk($diaria, 500) as $lote) {
        $nuevasD += DB::table('exp_historial_diaria')->insertOrIgnore($this->conFechas($lote));
      }
      foreach (array_chunk($control, 500) as $lote) {
        $nuevasC += DB::table('exp_historial_control')->insertOrIgnore($this->conFechas($lote));
      }
      if ($nuevasD + $nuevasC) {
        app(Auditoria::class)->manual('alta', 'Expedición', 'exp_historial_diaria', null,
          "Importación del historial de planillas: $nuevasD movimientos de Diaria y $nuevasC controles de Ctrol");
      }
    }

    $sinCliente = count(array_filter($control, fn($r) => !$r['cliente_numero']));
    $this->info('Diaria: ' . count($diaria) . " filas, nuevas $nuevasD · Ctrol: " . count($control) . " filas, nuevas $nuevasC"
      . " · Ctrol sin número de cliente reconocible: $sinCliente (quedan con el texto original)"
      . ($this->option('simular') ? ' (simulación, no se guardó nada)' : ''));
    return self::SUCCESS;
  }

  private function leer(string $archivo, callable $fila): array
  {
    $fh = fopen($archivo, 'r') ?: throw new \RuntimeException("No se puede abrir $archivo");
    $encabezado = fgetcsv($fh);
    $filas = [];
    while (($datos = fgetcsv($fh)) !== false) {
      $filas[] = $fila(array_combine($encabezado, array_map('trim', $datos)));
    }
    fclose($fh);
    return $filas;
  }

  private function conFechas(array $lote): array
  {
    $ahora = now();
    return array_map(fn($r) => $r + ['created_at' => $ahora, 'updated_at' => $ahora], $lote);
  }

  /** AAAAMMDD a fecha; si no es válida queda null y el valor original se guarda. */
  private function fecha(string $v, string $columna, array &$original): ?string
  {
    if ($v === '') {
      return null;
    }
    if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', substr($v, 0, 8), $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1]) && $m[1] >= 2000 && $m[1] <= 2100) {
      return "{$m[1]}-{$m[2]}-{$m[3]}";
    }
    $original[$columna] = $v;
    return null;
  }

  private function numero(string $v): ?int
  {
    return preg_match('/^\d{1,5}$/', $v) ? (int) $v : null;
  }

  private function corto(string $v, int $largo): ?string
  {
    return $v === '' ? null : mb_substr($v, 0, $largo);
  }
}
