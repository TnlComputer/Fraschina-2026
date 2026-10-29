<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Depósitos de la empresa (Tools → Depósitos). El principal es el punto de salida del reparto.
 * La ubicación (latitud/longitud) se busca al guardar la dirección y se puede corregir a mano.
 */
return new class extends Migration
{
  public function up(): void
  {
    Schema::create('depositos', function (Blueprint $table) {
      $table->id();
      $table->string('nombre', 100);
      $table->string('direccion', 150);
      $table->string('localidad', 150);
      $table->decimal('latitud', 10, 7)->nullable();
      $table->decimal('longitud', 10, 7)->nullable();
      $table->boolean('principal')->default(false);
      $table->text('observaciones')->nullable();
      $table->timestamps();
      $table->softDeletes();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('depositos');
  }
};
