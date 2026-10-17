<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Horarios de entrega sin cargar: "00:00:00" pasa a NULL ("sin horario"), como lo deja ahora la conversión
 * en cualquier motor (MySQL convertía lo vacío en 00:00:00 y MariaDB en NULL). La pantalla muestra "—".
 */
return new class extends Migration
{
  public function up(): void
  {
    foreach (['desde', 'hasta', 'desde1', 'hasta1'] as $c) {
      DB::table('distribucions')->where($c, '00:00:00')->update([$c => null]);
    }
  }

  public function down(): void
  {
    // No se revierte: NULL y 00:00:00 significaban lo mismo (sin horario).
  }
};
