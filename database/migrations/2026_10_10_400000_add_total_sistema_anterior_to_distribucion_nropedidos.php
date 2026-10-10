<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El sistema anterior guardaba el total del pedido calculado solo con las líneas que tenía al
 * crearse (no se actualizaba al agregar líneas). totalPedido pasa a ser la suma real de las líneas
 * y el valor original se conserva aquí.
 */
return new class extends Migration
{
  public function up(): void
  {
    Schema::table('distribucion_nropedidos', function (Blueprint $table) {
      $table->decimal('total_sistema_anterior', 12, 2)->nullable()->after('totalPedido');
    });
  }

  public function down(): void
  {
    Schema::table('distribucion_nropedidos', function (Blueprint $table) {
      $table->dropColumn('total_sistema_anterior');
    });
  }
};
