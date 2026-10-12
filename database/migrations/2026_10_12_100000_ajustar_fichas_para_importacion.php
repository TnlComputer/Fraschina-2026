<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fichas (cuentas corrientes por planilla) listas para importar el Excel Fichas.xlsx sin perder datos:
 * campos opcionales (las planillas tienen muchas celdas vacías), números más anchos, la fila de origen
 * y un campo "extra" con las columnas adicionales y los valores que no se pudieron convertir, tal cual.
 * Solo se aplica con la tabla vacía (hasta ahora nunca se cargó).
 */
return new class extends Migration
{
  public function up(): void
  {
    if (DB::table('fichas')->exists()) {
      throw new RuntimeException('La tabla fichas ya tiene datos: revisar antes de cambiar su estructura.');
    }

    Schema::drop('fichas');
    Schema::create('fichas', function (Blueprint $table) {
      $table->id();
      $table->string('origen_tabla', 50)->index();   // hoja del Excel (número de ficha)
      $table->unsignedInteger('fila');                 // fila en la hoja, para rastrear el origen
      $table->unsignedInteger('m')->nullable();        // M
      $table->unsignedInteger('pn')->nullable();       // PN
      $table->string('es', 5)->nullable();             // *
      $table->date('fe')->nullable();                  // FE
      $table->string('ord', 20)->nullable();           // OR
      $table->string('pl', 20)->nullable();            // PL
      $table->string('ca', 20)->nullable();            // CA
      $table->string('tb', 20)->nullable();            // T/B
      $table->decimal('k', 12, 2)->nullable();         // K
      $table->decimal('p', 14, 3)->nullable();         // P
      $table->decimal('i', 16, 2)->nullable();         // I
      $table->string('d', 10)->nullable();             // D
      $table->unsignedBigInteger('n')->nullable();     // N
      $table->date('fp')->nullable();                  // FP
      $table->integer('pp')->nullable();               // PP
      $table->text('o')->nullable();                   // O
      $table->json('extra')->nullable();               // columnas adicionales y valores originales no convertibles
      $table->timestamps();
      $table->softDeletes();
      $table->unique(['origen_tabla', 'fila']);
    });
  }

  public function down(): void
  {
    // No se revierte: volver a la estructura anterior perdería datos importados.
  }
};
