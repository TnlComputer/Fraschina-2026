<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Regla del sistema: no se borran datos. Las tablas que antes se borraban físicamente
 * pasan a borrado lógico (deleted_at): el registro queda en la base y la app no lo muestra.
 */
return new class extends Migration
{
  private array $tablas = [
    'distribucion_linea_pedidos',
    'distribucion_linea_tareas',
    'exp_pedido_lineas',
    'exp_notificacion_destinatarios',
    'expedicion_clientes',
    'expedicion_molinos',
    'expedicion_molinos_textos_clientes',
    'roles',
    'permissions',
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
