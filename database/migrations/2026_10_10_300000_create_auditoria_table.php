<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Log de operaciones: cada alta, modificación, eliminación o restauración, con usuario,
 * sector, registro afectado y valores anteriores/nuevos. Solo se agrega, nunca se modifica.
 */
return new class extends Migration
{
  public function up(): void
  {
    Schema::create('auditoria', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('user_id')->nullable();
      $table->string('usuario', 150)->nullable();
      // alta | modificacion | eliminacion | restauracion
      $table->string('accion', 20);
      $table->string('sector', 40);
      $table->string('tabla', 80);
      $table->string('registro_id', 40)->nullable();
      $table->string('descripcion')->nullable();
      $table->longText('cambios')->nullable();
      $table->string('ip', 45)->nullable();
      $table->string('url')->nullable();
      $table->timestamp('created_at')->useCurrent();

      $table->index('created_at');
      $table->index(['user_id', 'created_at']);
      $table->index(['sector', 'created_at']);
      $table->index(['tabla', 'registro_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('auditoria');
  }
};
