<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Marca "Flag" de las líneas de pedido (bandera en el sistema anterior), que no se había migrado.
 */
return new class extends Migration
{
  // Líneas marcadas en el sistema anterior (lineaPedidos.bandera = '*', hasta 2017)
  private const MARCADAS = [
    178, 188, 214, 215, 216, 217, 218, 255, 272, 278, 279, 280, 322, 333, 338, 343, 344, 368, 369, 385, 441, 488, 510, 533,
    565, 599, 672, 675, 684, 693, 698, 731, 811, 830, 835, 886, 957, 1033, 1038, 1098, 1237, 1320, 1383, 1583, 1585, 1673,
    1756, 1778, 1839, 1931, 2012, 2057, 2150, 5001,
  ];

  public function up(): void
  {
    // También en la línea base de la actualización desde el sistema anterior, si existe
    foreach (['distribucion_linea_pedidos', config('legado.prefijo_base') . 'distribucion_linea_pedidos'] as $tabla) {
      if (!Schema::hasTable($tabla) || Schema::hasColumn($tabla, 'bandera')) {
        continue;
      }
      Schema::table($tabla, fn(Blueprint $t) => $t->string('bandera', 2)->nullable()->after('totalLinea'));
      DB::table($tabla)->whereIn('id', self::MARCADAS)->update(['bandera' => '*']);
    }
  }

  public function down(): void
  {
    foreach (['distribucion_linea_pedidos', config('legado.prefijo_base') . 'distribucion_linea_pedidos'] as $tabla) {
      if (Schema::hasTable($tabla) && Schema::hasColumn($tabla, 'bandera')) {
        Schema::table($tabla, fn(Blueprint $t) => $t->dropColumn('bandera'));
      }
    }
  }
};
