<?php

namespace App\Console\Commands\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * Conversión de la base del sistema anterior a tablas de trabajo dentro de la base de la app
 * (con los scripts de conversion_tablas). La usan legado:sincronizar y legado:recargar.
 * El sistema anterior solo se lee.
 */
trait ConvierteLegado
{
  /** Tablas de la app que arman los scripts (en orden). */
  protected function tablasDelScript(): array
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
  protected function convertir(string $origen, string $prefijo): void
  {
    if (!preg_match('/^\w+$/', $origen) || !DB::selectOne('SELECT COUNT(*) n FROM information_schema.schemata WHERE schema_name = ?', [$origen])->n) {
      throw new \RuntimeException("No existe la base $origen.");
    }
    $app = DB::connection()->getDatabaseName();
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

    // Nombres reales de las tablas del sistema anterior: en Linux distinguen mayúsculas (auxChofer, CDagenda...)
    $nombres = [];
    foreach (DB::select('SELECT table_name t FROM information_schema.tables WHERE table_schema = ?', [$origen]) as $r) {
      $nombres[strtolower($r->t)] = $r->t;
    }

    $modo = DB::selectOne('SELECT @@SESSION.sql_mode m')->m;
    DB::statement("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");
    try {
      foreach (config('legado.scripts') as $s) {
        $sql = file_get_contents(base_path("conversion_tablas/$s"));
        $sql = preg_replace_callback('/\bfraschin_backup\.`?(\w+)`?/i',
          fn($m) => "`$origen`.`" . ($nombres[strtolower($m[1])] ?? $m[1]) . '`', $sql);
        $sql = preg_replace('/\bfraschina_2024\./i', "`$app`.{$prefijo}", $sql);
        // tablas que algún script escribe sin indicar la base
        $sql = preg_replace('/(INTO\s+)(proveedor_aux_productos|representacion_aux_productos)\b/i', "$1`$app`.{$prefijo}$2", $sql);
        DB::unprepared($sql);
      }
    } finally {
      DB::statement('SET SESSION sql_mode = ?', [$modo]);
    }
  }

  /** leg_hoy_* pasa a ser la línea base (leg_base_*) contra la que compara la actualización diaria. */
  protected function rotarBase(): void
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
