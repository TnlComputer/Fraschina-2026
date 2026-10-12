<?php

namespace App\Console\Commands;

use App\Models\MigracionDuda;
use App\Console\Commands\ExpedicionGeneralImportar;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Arma (o actualiza) las dudas de la migración de planillas para preguntarle al cliente desde la app.
 * Se calculan con los datos ya cargados; las respuestas que ya se escribieron no se tocan.
 */
class ExpedicionDudas extends Command
{
  protected $signature = 'expedicion:dudas {--general= : CSV de General (para los registros que no se cargaron)} {--planped= : JSON de Plan ped}';

  protected $description = 'Arma la lista de dudas de la migración de planillas de Expedición';

  public function handle(): int
  {
    $dudas = [];
    $molinos = DB::table('expedicion_molinos')->whereNotNull('codigo')->pluck('sigla', 'codigo');
    $clientes = DB::table('expedicion_clientes')->whereNotNull('numero')->pluck('nombre_planilla', 'numero');
    $cli = fn($n) => $n ? trim($n . ' ' . ($clientes[$n] ?? '')) : '';

    // 1. Molinos deducidos (no escritos en las planillas con su código)
    $dudas['molinos_deducidos'] = ['Planillas (Ctrl)', 'Molinos 1006 y 1008',
      'En las planillas no figura el nombre de los códigos 1006 y 1008. ¿Son correctos?',
      ['columnas' => ['Código', 'Molino asignado', 'Cómo se dedujo'], 'filas' => [
        [1006, 'Martelletti Hnos. Molino Argentino S.R.L.', 'Orden de columnas de la hoja de 2020 (Argenpam, Alberti, Basile, Cores, Luján, Martelletti, Moliendas)'],
        [1008, 'Molinos Benvenuto S.A.', 'Encabezado "Ben" en la hoja PS Silvia'],
      ]]];

    // 2. Fichas: número de ficha contra el cliente de Diaria
    $cruce = DB::table('fichas as f')
      ->join('exp_historial_diaria as d', fn($j) => $j->on('d.molino_codigo', '=', 'f.m')->on(DB::raw('d.pedido'), '=', DB::raw('CAST(f.pn AS CHAR)')))
      ->whereNotNull('f.pn')
      ->selectRaw('f.origen_tabla as ficha, d.cliente_numero as cliente, COUNT(DISTINCT f.m, f.pn) as pedidos')
      ->groupBy('f.origen_tabla', 'd.cliente_numero')->get()
      ->filter(fn($r) => (string) $r->cliente !== (string) $r->ficha);
    $dudas['fichas_otro_cliente'] = ['Fichas', 'Pedidos de una ficha que en Diaria figuran a nombre de otro cliente',
      'Estos pedidos están en una ficha pero en la Diaria están cargados para otro cliente. ¿Corresponden a esa ficha?',
      ['columnas' => ['Ficha', 'Cliente en Diaria', 'Pedidos'], 'filas' => $cruce->sortByDesc('pedidos')->map(fn($r) => [$cli($r->ficha), $cli($r->cliente), $r->pedidos])->values()->all()]];

    $sinCruce = DB::table('fichas')->whereIn('origen_tabla', ['46', '52'])->selectRaw('origen_tabla, COUNT(*) n')->groupBy('origen_tabla')->get();
    $dudas['fichas_sin_verificar'] = ['Fichas', 'Fichas sin pedidos para verificar el cliente',
      'Estas fichas no tienen pedidos que figuren en la Diaria, así que no se pudo comprobar que el número de ficha sea el del cliente. ¿Son esos clientes?',
      ['columnas' => ['Ficha', 'Cliente asignado', 'Filas'], 'filas' => $sinCruce->map(fn($r) => [$r->origen_tabla, $cli($r->origen_tabla), $r->n])->all()]];

    // 3. Plan ped
    if ($this->option('planped') && is_file($this->option('planped'))) {
      $pp = json_decode(file_get_contents($this->option('planped')), true);
      if (isset($pp['Que Rico'])) {
        $dudas['planped_que_rico'] = ['Plan ped', 'Planilla "Que Rico"',
          'La planilla dice cliente "' . $pp['Que Rico']['cliente'] . '". ¿Qué número de cliente es? (no se cargó hasta confirmarlo)',
          ['columnas' => ['Texto de la planilla'], 'filas' => array_map(fn($l) => [$l], $pp['Que Rico']['lineas'])]];
      }
    }
    $conNotas = DB::table('expedicion_clientes')->where('obs', 'like', '%Notas por molino:%')->orderBy('numero')->get(['numero', 'obs']);
    $dudas['planped_notas_molino'] = ['Plan ped', 'Notas por molino a revisar',
      'Estas notas estaban al costado de la planilla de cada cliente. Para que salgan impresas hay que indicar a qué molino corresponde cada una (se cargan en Expedición → Textos cliente-molino).',
      ['columnas' => ['Cliente', 'Notas'], 'filas' => $conNotas->map(fn($r) => [$cli($r->numero), preg_match('/Notas por molino: (.*)/', $r->obs, $m) ? $m[1] : ''])->all()]];

    // 4. General: certificados anteriores al corte que no coinciden con lo cargado (mismo cruce que expedicion:general)
    if ($this->option('general') && is_file($this->option('general'))) {
      $existentes = [];
      foreach (DB::table('expedicion_general')->get() as $r) {
        $k = ExpedicionGeneralImportar::clave((array) $r);
        $existentes[$k] = ($existentes[$k] ?? 0) + 1;
      }
      $fh = fopen($this->option('general'), 'r');
      $enc = fgetcsv($fh);
      $filas = [];
      while (($d = fgetcsv($fh)) !== false) {
        $r = array_combine($enc, $d);
        $reg = ['fecha' => $r['fecha']];
        foreach (['mo', 'cl', 'prod', 'p', 'l', 'w', 'gh', 'hum', 'cz', 'fn'] as $c) {
          $reg[$c] = ExpedicionGeneralImportar::valor($c, $r[$c]);
        }
        $k = ExpedicionGeneralImportar::clave($reg);
        if (($existentes[$k] ?? 0) > 0) {
          $existentes[$k]--;
        } else {
          $filas[] = [$r['fila'], $r['fecha'], $r['mo'], $r['cl'], $r['prod'], $r['p'], $r['l'], $r['w']];
        }
      }
      fclose($fh);
      $siglas = collect($filas)->filter(fn($f) => !preg_match('/^\d+$/', $f[2]))->countBy(fn($f) => $f[2]);
      $dudas['general_no_cargados'] = ['General', 'Certificados que no se cargaron (' . count($filas) . ')',
        'Estos certificados no coinciden con los que ya estaban en el sistema y son anteriores al 28/02/2025, por eso no se cargaron (pueden haberse corregido o dado de baja en el sistema anterior). ' .
        ($siglas->isNotEmpty() ? 'Varios tienen como molino una sigla en lugar del código: ' . $siglas->map(fn($n, $s) => "\"$s\" ($n)")->implode(', ') . '. ' : '') .
        '¿Hay que cargarlos? ¿Qué molino es cada sigla?',
        ['columnas' => ['Fila', 'Fecha', 'Molino', 'Cliente', 'Producto', 'P', 'L', 'W'], 'filas' => $filas]];
      MigracionDuda::where('clave', 'general_molino_texto')->whereNull('resuelta_at')->whereNull('respuesta')->delete();
    }

    // 5. Ctrol sin cliente reconocible y Diaria con fechas mal escritas
    $dudas['control_sin_cliente'] = ['Ctrl (Ctrol)', 'Controles sin número de cliente',
      'En estas filas no se reconoce el número de cliente al principio de la columna C. ¿De qué cliente son?',
      ['columnas' => ['Fila', 'Fecha', 'Molino', 'C (tal cual)', 'Transporte'], 'filas' => DB::table('exp_historial_control')->whereNull('cliente_numero')->orderBy('fila')
        ->get()->map(fn($r) => [$r->fila, $r->fecha, $r->molino_codigo ? $r->molino_codigo . ' ' . ($molinos[$r->molino_codigo] ?? '') : '', $r->detalle, $r->transporte])->all()]];
    $dudas['historial_fechas'] = ['Diaria / Ctrol', 'Fechas mal escritas en el historial',
      'Estas filas tienen una fecha que no es válida (quedó guardada tal cual). ¿Cuál es la fecha correcta?',
      ['columnas' => ['Planilla', 'Fila', 'Valor original'], 'filas' => collect()
        ->merge(DB::table('exp_historial_diaria')->whereNotNull('original')->get()->map(fn($r) => ['Diaria', $r->fila, $r->original]))
        ->merge(DB::table('exp_historial_control')->whereNotNull('original')->get()->map(fn($r) => ['Ctrol', $r->fila, $r->original]))->all()]];

    // 6. Pedidos vigentes
    $dudas['pedidos_numeracion'] = ['Diaria', 'Numeración de los pedidos vigentes',
      'Los pedidos de la Diaria usan números por molino (ej. 5117 en Luján y 1116 en Moliendas) que se repiten entre molinos. La app usa un solo número correlativo (con -0, -1 para las modificaciones). ' .
      '¿Desde qué número arrancan los pedidos en la app? Los números viejos (PN / RA) quedan guardados en cada pedido.', null];

    $nuevas = 0;
    foreach ($dudas as $clave => [$origen, $tema, $pregunta, $detalle]) {
      if ($detalle !== null && empty($detalle['filas'])) {
        MigracionDuda::where('clave', $clave)->whereNull('resuelta_at')->whereNull('respuesta')->delete();  // ya no hay nada que preguntar
        continue;
      }
      $duda = MigracionDuda::firstOrNew(['clave' => $clave]);
      $nuevas += $duda->exists ? 0 : 1;
      $duda->fill(compact('origen', 'tema', 'pregunta', 'detalle'))->save();   // la respuesta, si la hay, no se toca
    }

    $this->info('Dudas: ' . MigracionDuda::count() . " (nuevas $nuevas), pendientes: " . MigracionDuda::whereNull('resuelta_at')->count());
    return self::SUCCESS;
  }
}
