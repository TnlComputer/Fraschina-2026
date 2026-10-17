<?php

namespace App\Console\Commands;

use App\Models\MigracionDuda;
use App\Services\Auditoria;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Trae a la app lo que cambió en el sistema anterior (altas, modificaciones y bajas), todas las noches.
 *
 * Cómo funciona:
 *  1. Convierte la base del sistema anterior con los mismos scripts de la migración (conversion_tablas) a tablas
 *     de trabajo leg_hoy_* dentro de la base de la app. El sistema anterior solo se lee.
 *  2. Compara leg_hoy_* con leg_base_* (la conversión de la noche anterior): lo que difiere es lo que cambió allá.
 *  3. Aplica en las tablas de la app solo esas filas. Si una fila también se modificó en la app nueva no se pisa:
 *     queda como conflicto en Expedición → Dudas de migración. Las bajas se marcan (status 'D' / deleted_at), nunca se borran.
 *  4. leg_hoy_* pasa a ser la nueva base.
 *
 * Primera vez: --base=<base con la copia del sistema anterior que se usó para cargar la app> arma leg_base_*.
 */
class LegadoSincronizar extends Command
{
  protected $signature = 'legado:sincronizar
    {--origen= : Base del sistema anterior (por defecto config legado.origen)}
    {--base= : Arma la línea base desde esta base (copia del sistema anterior usada para cargar la app) y termina}
    {--simular : Muestra lo que cambiaría sin aplicar nada}';

  protected $description = 'Actualiza la app con lo que cambió en el sistema anterior';

  private string $app;
  private array $resumen = [];
  private array $conflictos = [];

  public function handle(): int
  {
    if (DB::getDriverName() !== 'mysql') {
      $this->error('Solo funciona con MySQL / MariaDB.');
      return self::FAILURE;
    }
    $this->app = DB::connection()->getDatabaseName();

    if ($base = $this->option('base')) {
      $this->convertir($base, config('legado.prefijo_base'));
      $this->info("Línea base armada desde $base.");
      return self::SUCCESS;
    }

    $origen = $this->option('origen') ?: config('legado.origen');
    if (!$origen) {
      $this->warn('No hay base del sistema anterior configurada (LEGADO_DB): no se sincroniza.');
      return self::SUCCESS;
    }
    if (!$this->tablasDelScript() || !Schema::hasTable(config('legado.prefijo_base') . 'distribucions')) {
      $this->error('Falta la línea base: correr primero con --base=<copia del sistema anterior usada para la carga>.');
      return self::FAILURE;
    }

    $inicio = microtime(true);
    $this->convertir($origen, config('legado.prefijo_hoy'));
    // Tablas base faltantes (tabla nueva en los scripts: todo es alta). Antes de la transacción: en MySQL,
    // CREATE TABLE confirma la transacción en curso.
    foreach ($this->tablasDelScript() as $tabla) {
      $base = config('legado.prefijo_base') . $tabla;
      if (!Schema::hasTable($base)) {
        DB::statement("CREATE TABLE `$base` LIKE `" . config('legado.prefijo_hoy') . "$tabla`");
      }
    }

    DB::beginTransaction();
    DB::statement('SET FOREIGN_KEY_CHECKS = 0');
    try {
      foreach ($this->tablasDelScript() as $tabla) {
        $this->sincronizarTabla($tabla);
      }
      DB::statement('SET FOREIGN_KEY_CHECKS = 1');
      if ($this->option('simular')) {
        DB::rollBack();
      } else {
        DB::commit();
      }
    } catch (\Throwable $e) {
      DB::statement('SET FOREIGN_KEY_CHECKS = 1');
      DB::rollBack();
      $this->error('No se aplicó nada: ' . $e->getMessage());
      report($e);
      return self::FAILURE;
    }

    if (!$this->option('simular')) {
      $this->rotarBase();
      $this->registrarConflictos();
      $total = array_sum(array_map(fn($r) => $r[1] + $r[2] + $r[3], $this->resumen));
      if ($total) {
        app(Auditoria::class)->manual('modificacion', 'Sistema anterior', 'legado', null,
          "Actualización desde el sistema anterior: $total cambios en " . count($this->resumen) . ' tablas', ['tablas' => [null, implode(', ', array_keys($this->resumen))]]);
      }
    }

    $this->table(['Tabla', 'Nuevas', 'Modificadas', 'Bajas', 'Conflictos'], array_map(fn($t, $r) => [$t, ...$r], array_keys($this->resumen), $this->resumen));
    $this->info(sprintf('Listo en %.0f s%s. Conflictos: %d', microtime(true) - $inicio, $this->option('simular') ? ' (simulación, no se aplicó nada)' : '', count($this->conflictos)));
    return self::SUCCESS;
  }

  /** Tablas de la app que arman los scripts (en orden). */
  private function tablasDelScript(): array
  {
    static $tablas = null;
    if ($tablas === null) {
      $tablas = [];
      foreach (config('legado.scripts') as $s) {
        preg_match_all('/INTO\s+(?:fraschina_2024\.)?`?(\w+)`?\s*\(/i', file_get_contents(base_path("conversion_tablas/$s")), $m);
        $tablas = array_merge($tablas, $m[1]);
      }
      $tablas = array_values(array_unique($tablas));
    }
    return $tablas;
  }

  /** Corre los scripts de conversión desde $origen hacia tablas $prefijo* de la base de la app. */
  private function convertir(string $origen, string $prefijo): void
  {
    if (!preg_match('/^\w+$/', $origen) || !DB::selectOne('SELECT COUNT(*) n FROM information_schema.schemata WHERE schema_name = ?', [$origen])->n) {
      throw new \RuntimeException("No existe la base $origen.");
    }
    // Tablas de trabajo: las que escriben los scripts y las que leen (copiadas de la app)
    $escritas = $this->tablasDelScript();
    $leidas = [];
    foreach (config('legado.scripts') as $s) {
      preg_match_all('/fraschina_2024\.`?(\w+)`?/i', file_get_contents(base_path("conversion_tablas/$s")), $m);
      $leidas = array_merge($leidas, $m[1]);
    }
    foreach (array_unique(array_merge($escritas, $leidas)) as $t) {
      DB::statement("DROP TABLE IF EXISTS `{$prefijo}{$t}`");
      DB::statement("CREATE TABLE `{$prefijo}{$t}` LIKE `$t`");
      if (!in_array($t, $escritas, true)) {
        DB::statement("INSERT INTO `{$prefijo}{$t}` SELECT * FROM `$t`");
      }
    }

    $modo = DB::selectOne('SELECT @@SESSION.sql_mode m')->m;
    DB::statement("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");
    try {
      foreach (config('legado.scripts') as $s) {
        $sql = file_get_contents(base_path("conversion_tablas/$s"));
        $sql = preg_replace('/\bfraschin_backup\./i', "`$origen`.", $sql);
        $sql = preg_replace('/\bfraschina_2024\./i', "`{$this->app}`.{$prefijo}", $sql);
        // tablas que algún script escribe sin indicar la base
        $sql = preg_replace('/(INTO\s+)(proveedor_aux_productos|representacion_aux_productos)\b/i', "$1`{$this->app}`.{$prefijo}$2", $sql);
        DB::unprepared($sql);
      }
    } finally {
      DB::statement('SET SESSION sql_mode = ?', [$modo]);
    }
  }

  private function sincronizarTabla(string $tabla): void
  {
    $hoy = config('legado.prefijo_hoy') . $tabla;
    $base = config('legado.prefijo_base') . $tabla;
    $cols = array_values(array_diff(
      array_intersect(Schema::getColumnListing($hoy), Schema::getColumnListing($base), Schema::getColumnListing($tabla)),
      config('legado.ignorar'), ['id']
    ));
    $distinto = fn($a, $b) => 'NOT (' . implode(' AND ', array_map(fn($c) => "$a.`$c` <=> $b.`$c`", $cols)) . ')';
    $igual = fn($a, $b) => '(' . implode(' AND ', array_map(fn($c) => "$a.`$c` <=> $b.`$c`", $cols)) . ')';
    $lista = implode(', ', array_map(fn($c) => "`$c`", $cols));
    $ahora = now()->toDateTimeString();
    $tieneTimestamps = Schema::hasColumn($tabla, 'updated_at');
    $n = ['nuevas' => 0, 'modificadas' => 0, 'bajas' => 0, 'conflictos' => 0];

    // Altas (en el sistema anterior hoy y no ayer)
    $nuevas = DB::select("SELECT h.id FROM `$hoy` h LEFT JOIN `$base` b ON b.id = h.id WHERE b.id IS NULL");
    foreach ($nuevas as $r) {
      $app = DB::table($tabla)->where('id', $r->id)->exists();
      if (!$app) {
        DB::statement("INSERT INTO `$tabla` (id, $lista" . ($tieneTimestamps ? ', created_at, updated_at' : '') . ") SELECT id, $lista" . ($tieneTimestamps ? ", '$ahora', '$ahora'" : '') . " FROM `$hoy` WHERE id = ?", [$r->id]);
        $n['nuevas']++;
      } elseif (!DB::selectOne("SELECT COUNT(*) n FROM `$tabla` a JOIN `$hoy` h ON h.id = a.id WHERE a.id = ? AND {$igual('a', 'h')}", [$r->id])->n) {
        $this->conflicto($tabla, $r->id, 'Alta en el sistema anterior con un id que ya existe en la app nueva');
        $n['conflictos']++;
      }
    }

    // Modificaciones
    $modificadas = DB::select("SELECT h.id FROM `$hoy` h JOIN `$base` b ON b.id = h.id WHERE {$distinto('h', 'b')}");
    foreach ($modificadas as $r) {
      $sinTocarEnApp = DB::selectOne("SELECT COUNT(*) n FROM `$tabla` a JOIN `$base` b ON b.id = a.id WHERE a.id = ? AND {$igual('a', 'b')}", [$r->id])->n;
      $yaIgual = DB::selectOne("SELECT COUNT(*) n FROM `$tabla` a JOIN `$hoy` h ON h.id = a.id WHERE a.id = ? AND {$igual('a', 'h')}", [$r->id])->n;
      if ($yaIgual) {
        continue;
      }
      if ($sinTocarEnApp) {
        $set = implode(', ', array_map(fn($c) => "a.`$c` = h.`$c`", $cols)) . ($tieneTimestamps ? ", a.updated_at = '$ahora'" : '');
        DB::statement("UPDATE `$tabla` a JOIN `$hoy` h ON h.id = a.id SET $set WHERE a.id = ?", [$r->id]);
        $n['modificadas']++;
      } elseif (!DB::table($tabla)->where('id', $r->id)->exists()) {
        DB::statement("INSERT INTO `$tabla` (id, $lista" . ($tieneTimestamps ? ', created_at, updated_at' : '') . ") SELECT id, $lista" . ($tieneTimestamps ? ", '$ahora', '$ahora'" : '') . " FROM `$hoy` WHERE id = ?", [$r->id]);
        $n['nuevas']++;
      } else {
        $this->conflicto($tabla, $r->id, 'Modificado en los dos sistemas: no se pisó lo de la app nueva');
        $n['conflictos']++;
      }
    }

    // Bajas (ayer estaba y hoy no): se marcan, no se borran
    $bajas = DB::select("SELECT b.id FROM `$base` b LEFT JOIN `$hoy` h ON h.id = b.id WHERE h.id IS NULL");
    foreach ($bajas as $r) {
      $sinTocarEnApp = DB::selectOne("SELECT COUNT(*) n FROM `$tabla` a JOIN `$base` b ON b.id = a.id WHERE a.id = ? AND {$igual('a', 'b')}", [$r->id])->n;
      if (!$sinTocarEnApp) {
        if (DB::table($tabla)->where('id', $r->id)->exists()) {
          $this->conflicto($tabla, $r->id, 'Borrado en el sistema anterior pero modificado en la app nueva: no se marcó la baja');
          $n['conflictos']++;
        }
        continue;
      }
      if (Schema::hasColumn($tabla, 'deleted_at')) {
        DB::table($tabla)->where('id', $r->id)->update(['deleted_at' => $ahora]);
      } elseif (Schema::hasColumn($tabla, 'status')) {
        DB::table($tabla)->where('id', $r->id)->update(['status' => 'D']);
      } else {
        $this->conflicto($tabla, $r->id, 'Borrado en el sistema anterior (la tabla no tiene marca de baja)');
        $n['conflictos']++;
        continue;
      }
      $n['bajas']++;
    }

    if (array_sum($n)) {
      $this->resumen[$tabla] = array_values($n);
    }
  }

  private function conflicto(string $tabla, $id, string $motivo): void
  {
    $this->conflictos[] = [$tabla, $id, $motivo, now()->format('d/m/Y')];
  }

  private function registrarConflictos(): void
  {
    if (!$this->conflictos) {
      return;
    }
    $duda = MigracionDuda::firstOrNew(['clave' => 'legado_conflictos']);
    $previas = $duda->exists && !$duda->resuelta_at ? ($duda->detalle['filas'] ?? []) : [];
    $duda->fill([
      'origen' => 'Sistema anterior',
      'tema' => 'Cambios del sistema anterior que no se aplicaron',
      'pregunta' => 'En la actualización diaria, estos registros cambiaron en el sistema anterior pero también se habían modificado en la app nueva (o el id ya existía). No se pisó nada: hay que revisar cuál es el dato correcto.',
      'detalle' => ['columnas' => ['Tabla', 'Id', 'Motivo', 'Fecha'], 'filas' => array_merge($previas, $this->conflictos)],
      'resuelta_at' => null,
    ])->save();
  }

  private function rotarBase(): void
  {
    $hoy = config('legado.prefijo_hoy');
    $base = config('legado.prefijo_base');
    foreach (DB::select("SELECT table_name t FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name LIKE ?", [str_replace('_', '\\_', $hoy) . '%']) as $r) {
      $t = substr($r->t, strlen($hoy));
      DB::statement("DROP TABLE IF EXISTS `{$base}{$t}`");
      DB::statement("RENAME TABLE `{$hoy}{$t}` TO `{$base}{$t}`");
    }
  }
}
