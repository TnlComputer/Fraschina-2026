<?php

use App\Services\Auditoria;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mientras el sistema anterior siga en uso, sus registros nuevos se traen cada noche con su id original
 * (legado:sincronizar). Para que nunca choquen ids:
 *   - hasta 49.999.998: registros del sistema anterior (id original)
 *   - 49.999.999: cliente "Sin cliente (sistema anterior)"
 *   - 50.000.000 + cliente * 20000 + días desde 2000: pedidos borrador creados en la migración
 *   - desde 100.000.000: todo lo que se crea en la app nueva (AUTO_INCREMENT)
 * Antes los registros de reemplazo tenían ids a continuación del último del sistema anterior (695 y 17582..17613),
 * que el sistema anterior volvió a usar. Solo se mueven ids de registros creados por la migración; no se borra nada.
 */
return new class extends Migration
{
  private const SIN_CLIENTE = 49999999;
  private const OBS_BORRADOR = 'Borrador sin confirmar en el sistema anterior: líneas cargadas sin número de pedido.';
  private const DESDE_APP = 100000000;

  public function up(): void
  {
    if (DB::getDriverName() !== 'mysql') {
      return;   // los tests (SQLite) no tienen estos datos
    }
    $refsCliente = array_column(DB::select("SELECT table_name t FROM information_schema.columns WHERE table_schema = DATABASE() AND column_name = 'distribucion_id' AND table_name NOT LIKE 'leg\\_%'"), 't');
    $refsPedido = ['distribucion_linea_pedidos', 'distribucion_linea_tareas', 'distribucion_agenda'];
    $movidos = ['clientes' => 0, 'pedidos' => 0];

    DB::statement('SET FOREIGN_KEY_CHECKS = 0');
    try {
      // 1. Cliente "Sin cliente"
      $sin = DB::table('distribucions')->where('razonsocial', 'Sin cliente (sistema anterior)')->value('id');
      if ($sin && (int) $sin !== self::SIN_CLIENTE) {
        DB::table('distribucions')->where('id', $sin)->update(['id' => self::SIN_CLIENTE]);
        foreach ($refsCliente as $t) {
          DB::table($t)->where('distribucion_id', $sin)->update(['distribucion_id' => self::SIN_CLIENTE]);
        }
        $movidos['clientes']++;
      }

      // 2. Pedidos borrador: id fijo según cliente y fecha
      foreach (DB::table('distribucion_nropedidos')->where('observaciones', self::OBS_BORRADOR)->get(['id', 'distribucion_id', 'fecha']) as $p) {
        $cliente = (int) $p->distribucion_id >= self::SIN_CLIENTE ? 0 : (int) $p->distribucion_id;
        $dias = $p->fecha ? max((int) DB::selectOne("SELECT DATEDIFF(?, '2000-01-01') + 1 d", [$p->fecha])->d, 0) : 0;
        $nuevo = 50000000 + $cliente * 20000 + $dias;
        if ((int) $p->id === $nuevo) {
          continue;
        }
        if (DB::table('distribucion_nropedidos')->where('id', $nuevo)->exists()) {
          throw new RuntimeException("El id $nuevo ya está ocupado (pedido borrador {$p->id}).");
        }
        DB::table('distribucion_nropedidos')->where('id', $p->id)->update(['id' => $nuevo]);
        foreach ($refsPedido as $t) {
          DB::table($t)->where('pedido_id', $p->id)->update(['pedido_id' => $nuevo]);
        }
        $movidos['pedidos']++;
      }
    } finally {
      DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    // 3. Lo que se cree en la app nueva arranca en 100.000.000 (tablas que vienen del sistema anterior)
    $tablas = [];
    foreach (glob(base_path('conversion_tablas/00-[0-3]*.sql')) as $f) {
      preg_match_all('/INTO\s+(?:fraschina_2024\.)?`?(\w+)`?\s*\(/i', file_get_contents($f), $m);
      $tablas = array_merge($tablas, $m[1]);
    }
    foreach (array_unique($tablas) as $t) {
      if (Schema::hasTable($t) && (int) DB::table($t)->max('id') < self::DESDE_APP) {
        DB::statement("ALTER TABLE `$t` AUTO_INCREMENT = " . self::DESDE_APP);
      }
    }

    if ($movidos['clientes'] || $movidos['pedidos']) {
      app(Auditoria::class)->manual('modificacion', 'Distribución', 'distribucion_nropedidos', null,
        "Ids fijos para registros creados en la migración: {$movidos['clientes']} cliente y {$movidos['pedidos']} pedidos borrador");
    }
  }

  public function down(): void
  {
    // No se revierte (los ids nuevos evitan choques con el sistema anterior).
  }
};
