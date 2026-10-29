<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ubicación en el mapa de cada cliente de Distribución, para sugerir el orden del reparto.
 * Se busca una sola vez; ubicacion_direccion guarda la dirección buscada y si cambia se vuelve a buscar.
 */
return new class extends Migration
{
  public function up(): void
  {
    Schema::table('distribucions', function (Blueprint $table) {
      $table->decimal('latitud', 10, 7)->nullable();
      $table->decimal('longitud', 10, 7)->nullable();
      $table->string('ubicacion_direccion', 255)->nullable();
    });
  }

  public function down(): void
  {
    Schema::table('distribucions', function (Blueprint $table) {
      $table->dropColumn(['latitud', 'longitud', 'ubicacion_direccion']);
    });
  }
};
