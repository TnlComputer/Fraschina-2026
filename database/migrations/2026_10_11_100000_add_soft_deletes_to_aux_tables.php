<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tablas auxiliares con borrado lógico: un valor eliminado queda en la base y deja de ofrecerse.
 */
return new class extends Migration
{
  private array $tablas = [
    'auxcalles', 'auxbarrios', 'auxmunicipios', 'auxlocalidades', 'auxzonas', 'auxfamilias', 'auxrubros',
    'auxcargos', 'auxmodos', 'auxtamanio', 'auxareas', 'auxhoras', 'auxcontacto', 'auxprioridades',
    'auxestados', 'auxtipopersonal', 'auxacciones', 'auxveraz', 'distribucion_tareas',
  ];

  public function up(): void
  {
    foreach ($this->tablas as $tabla) {
      if (Schema::hasTable($tabla) && !Schema::hasColumn($tabla, 'deleted_at')) {
        Schema::table($tabla, fn(Blueprint $table) => $table->softDeletes());
      }
    }
  }

  public function down(): void
  {
    foreach ($this->tablas as $tabla) {
      if (Schema::hasTable($tabla) && Schema::hasColumn($tabla, 'deleted_at')) {
        Schema::table($tabla, fn(Blueprint $table) => $table->dropSoftDeletes());
      }
    }
  }
};
