<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dudas de la migración de las planillas, para consultarlas con el cliente desde la app y dejar la respuesta.
 */
return new class extends Migration
{
  public function up(): void
  {
    Schema::create('migracion_dudas', function (Blueprint $table) {
      $table->id();
      $table->string('clave', 60)->unique();      // identifica la duda (la arma el comando expedicion:dudas)
      $table->string('origen', 60);                // planilla de donde surge
      $table->string('tema', 150);
      $table->text('pregunta');
      $table->json('detalle')->nullable();         // {"columnas": [...], "filas": [[...], ...]}
      $table->text('respuesta')->nullable();
      $table->timestamp('resuelta_at')->nullable();
      $table->foreignId('resuelta_por')->nullable()->constrained('users');
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('migracion_dudas');
  }
};
