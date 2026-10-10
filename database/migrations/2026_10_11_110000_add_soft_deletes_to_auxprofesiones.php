<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Profesiones con borrado lógico, igual que el resto de las tablas auxiliares.
 */
return new class extends Migration
{
  public function up(): void
  {
    if (Schema::hasTable('auxprofesiones') && !Schema::hasColumn('auxprofesiones', 'deleted_at')) {
      Schema::table('auxprofesiones', fn(Blueprint $table) => $table->softDeletes());
    }
  }

  public function down(): void
  {
    if (Schema::hasTable('auxprofesiones') && Schema::hasColumn('auxprofesiones', 'deleted_at')) {
      Schema::table('auxprofesiones', fn(Blueprint $table) => $table->dropSoftDeletes());
    }
  }
};
