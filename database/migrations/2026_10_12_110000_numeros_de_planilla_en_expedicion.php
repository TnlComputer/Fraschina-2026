<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Números reales de las planillas de Expedición (Ctrl, Diaria, Fichas, General), sin tocar los ids internos:
 * - expedicion_molinos.codigo: 1001..1008 (columna M de las planillas)
 * - expedicion_clientes.numero: 1..60 (columna C de Diaria/Ctrol y número de hoja de Fichas)
 * - expedicion_clientes.nombre_planilla: nombre corto de la planilla (la sigla tiene solo 5 caracteres)
 */
return new class extends Migration
{
  public function up(): void
  {
    Schema::table('expedicion_molinos', function (Blueprint $table) {
      $table->unsignedSmallInteger('codigo')->nullable()->unique()->after('id');
    });
    Schema::table('expedicion_clientes', function (Blueprint $table) {
      $table->unsignedSmallInteger('numero')->nullable()->unique()->after('id');
      $table->string('nombre_planilla', 100)->nullable()->after('sigla');
    });
  }

  public function down(): void
  {
    Schema::table('expedicion_molinos', fn(Blueprint $table) => $table->dropColumn('codigo'));
    Schema::table('expedicion_clientes', fn(Blueprint $table) => $table->dropColumn(['numero', 'nombre_planilla']));
  }
};
