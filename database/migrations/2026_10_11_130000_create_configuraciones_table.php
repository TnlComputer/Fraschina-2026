<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Parámetros de la app que se cambian desde Herramientas → Configuración (modo de envío de correos,
 * consultas a molinos, numeración de pedidos...). Sin fila = se usa el valor por defecto.
 */
return new class extends Migration
{
  public function up(): void
  {
    Schema::create('configuraciones', function (Blueprint $table) {
      $table->id();
      $table->string('clave', 100)->unique();
      $table->text('valor')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('configuraciones');
  }
};
