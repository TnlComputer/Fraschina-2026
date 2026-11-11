<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\ConvierteLegado;
use App\Models\MigracionDuda;
use App\Services\Auditoria;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Recarga completa desde el sistema anterior (decisión del 11/11/2026: "ponernos al día").
 *
 * Vacía las tablas que arman los scripts de conversion_tablas (auxiliares, Distribución, agendas, agro, molinos,
 * proveedores, transportes, representaciones) y las vuelve a cargar con lo que hay hoy en el sistema anterior.
 * No toca nada más: usuarios, roles y permisos, choferes, depósitos, Expedición, Tools (configuración, auditoría,
 * dudas de migración), fichas y cualquier tabla que no venga del sistema anterior quedan como están.
 *
 *  - Lo creado solo en la app nueva en esas tablas (id desde 100.000.000) se descarta: hacer un respaldo completo antes.
 *  - La ubicación en el mapa de los clientes (latitud, longitud, dirección buscada) se conserva por número de cliente.
 *  - Lo nuevo de la app vuelve a numerarse desde 100.000.000.
 *  - La conversión queda como línea base de la actualización diaria (legado:sincronizar), que sigue desde acá.
 *
 * Primero --simular (convierte y muestra cuántos registros quedarían, sin tocar la app), después --confirmar.
 */
class LegadoRecargar extends Command
{
  use ConvierteLegado;

  private const DESDE_APP = 100000000;
  private const UBICACIONES = 'leg_ubicaciones_recarga';

  protected $signature = 'legado:recargar
    {--origen= : Base del sistema anterior (por defecto config legado.origen)}
    {--simular : Convierte y muestra cuántos registros quedarían, sin modificar la app}
    {--confirmar : Vacía y recarga de verdad (hacer antes un respaldo completo de la base)}';

  protected $description = 'Vacía las tablas que vienen del sistema anterior y las vuelve a cargar desde él';

  public function handle(): int
  {
    if (DB::getDriverName() !== 'mysql') {
      $this->error('Solo funciona con MySQL / MariaDB.');
      return self::FAILURE;
    }
    $simular = (bool) $this->option('simular');
    if (!$simular && !$this->option('confirmar')) {
      $this->error('Esto vacía las tablas del sistema anterior en la app. Correr primero con --simular y, con el respaldo hecho, con --confirmar.');
      return self::FAILURE;
    }
    $origen = $this->option('origen') ?: config('legado.origen');
    if (!$origen) {
      $this->error('No hay base del sistema anterior configurada (LEGADO_DB o --origen).');
      return self::FAILURE;
    }

    $inicio = microtime(true);
    $hoy = config('legado.prefijo_hoy');
    $tablas = $this->tablasDelScript();

    $this->info("Convirtiendo $origen (solo lectura)...");
    $this->convertir($origen, $hoy);

    $cuentas = [];
    foreach ($tablas as $t) {
      $cuentas[$t] = [
        'antes' => DB::table($t)->count(),
        'app' => DB::table($t)->where('id', '>=', self::DESDE_APP)->count(),
        'anterior' => DB::table($hoy . $t)->count(),
      ];
    }
    $conUbicacion = Schema::hasColumn('distribucions', 'latitud');

    if ($simular) {
      $this->table(['Tabla', 'Hoy en la app', 'Creadas en la app (se descartan)', 'Quedarían (sistema anterior)'],
        array_map(fn($t, $c) => [$t, $c['antes'], $c['app'], $c['anterior']], array_keys($cuentas), $cuentas));
      if ($conUbicacion) {
        $this->line('Clientes con ubicación que se conservan: ' . DB::table('distribucions')->whereNotNull('latitud')->where('id', '<', self::DESDE_APP)->count());
      }
      foreach (DB::select("SELECT table_name t FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name LIKE ?", [str_replace('_', '\\_', $hoy) . '%']) as $r) {
        DB::statement("DROP TABLE IF EXISTS `{$r->t}`");
      }
      $this->info(sprintf('Simulación en %.0f s: no se modificó nada.', microtime(true) - $inicio));
      return self::SUCCESS;
    }

    // Ubicaciones de los clientes (no existen en el sistema anterior). Fuera de la transacción: CREATE TABLE confirma.
    if ($conUbicacion) {
      DB::statement('DROP TABLE IF EXISTS `' . self::UBICACIONES . '`');
      DB::statement('CREATE TABLE `' . self::UBICACIONES . '` AS SELECT id, latitud, longitud, ubicacion_direccion FROM distribucions WHERE latitud IS NOT NULL OR longitud IS NOT NULL OR ubicacion_direccion IS NOT NULL');
    }

    $this->info('Vaciando y recargando ' . count($tablas) . ' tablas...');
    DB::beginTransaction();
    DB::statement('SET FOREIGN_KEY_CHECKS = 0');
    try {
      foreach ($tablas as $t) {
        DB::statement("DELETE FROM `$t`");
        DB::statement("INSERT INTO `$t` SELECT * FROM `{$hoy}{$t}`");
      }
      if ($conUbicacion) {
        DB::statement('UPDATE distribucions d JOIN `' . self::UBICACIONES . '` u ON u.id = d.id SET d.latitud = u.latitud, d.longitud = u.longitud, d.ubicacion_direccion = u.ubicacion_direccion');
      }
      DB::statement('SET FOREIGN_KEY_CHECKS = 1');
      DB::commit();
    } catch (\Throwable $e) {
      DB::statement('SET FOREIGN_KEY_CHECKS = 1');
      DB::rollBack();
      $this->error('No se aplicó nada: ' . $e->getMessage());
      report($e);
      return self::FAILURE;
    }

    // Lo que se cree en la app nueva sigue numerándose desde 100.000.000 (ALTER confirma: va después)
    foreach ($tablas as $t) {
      if ((int) DB::table($t)->max('id') < self::DESDE_APP) {
        DB::statement("ALTER TABLE `$t` AUTO_INCREMENT = " . self::DESDE_APP);
      }
    }

    // La actualización diaria compara desde esta carga
    $this->rotarBase();

    // Los conflictos pendientes de la actualización diaria quedaron resueltos por la recarga
    $duda = MigracionDuda::where('clave', 'legado_conflictos')->whereNull('resuelta_at')->first();
    $duda?->update(['resuelta_at' => now()]);

    $despues = [];
    foreach ($tablas as $t) {
      $despues[$t] = DB::table($t)->count();
    }
    $ubicaciones = $conUbicacion ? DB::table('distribucions')->whereNotNull('latitud')->count() : 0;
    $total = array_sum($despues);

    app(Auditoria::class)->manual('modificacion', 'Sistema anterior', 'legado', null,
      "Recarga completa desde el sistema anterior ($origen): " . count($tablas) . " tablas, $total registros",
      ['tablas' => [null, implode(', ', $tablas)]]);

    $this->table(['Tabla', 'Antes', 'Creadas en la app (descartadas)', 'Sistema anterior', 'Ahora'],
      array_map(fn($t, $c) => [$t, $c['antes'], $c['app'], $c['anterior'], $despues[$t]], array_keys($cuentas), $cuentas));
    $distintas = array_keys(array_filter($cuentas, fn($c, $t) => $c['anterior'] !== $despues[$t], ARRAY_FILTER_USE_BOTH));
    if ($distintas) {
      $this->error('No coinciden con el sistema anterior: ' . implode(', ', $distintas));
    }
    $this->info(sprintf('Listo en %.0f s: %d registros. Clientes con ubicación: %d.', microtime(true) - $inicio, $total, $ubicaciones));
    return $distintas ? self::FAILURE : self::SUCCESS;
  }
}
