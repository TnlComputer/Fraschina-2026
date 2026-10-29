<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Choferes del reparto (Tools → Choferes). El código es lo que se escribe en el campo Chofer de Reparto (ej. "H").
 */
return new class extends Migration
{
  public function up(): void
  {
    Schema::create('choferes', function (Blueprint $table) {
      $table->id();
      $table->string('codigo', 2);
      $table->string('nombre', 100);
      $table->string('telefono', 50)->nullable();
      $table->timestamps();
      $table->softDeletes();
    });

    DB::table('choferes')->insert(['codigo' => 'H', 'nombre' => 'Romualdo González', 'created_at' => now(), 'updated_at' => now()]);
  }

  public function down(): void
  {
    Schema::dropIfExists('choferes');
  }
};
