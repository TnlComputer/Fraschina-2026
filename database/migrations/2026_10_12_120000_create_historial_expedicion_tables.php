<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historial de Expedición importado de las planillas, para consultar (no se edita):
 * - exp_historial_diaria: hoja "Diaria" (cada movimiento de pedidos desde 2020)
 * - exp_historial_control: hoja "Ctrol" de Ctrl.xlsx (control de entregas desde 2020)
 * Las columnas para filtrar (fecha, molino, cliente) se interpretan; el resto queda como texto, tal cual la planilla.
 */
return new class extends Migration
{
  public function up(): void
  {
    Schema::create('exp_historial_diaria', function (Blueprint $table) {
      $table->id();
      $table->unsignedInteger('fila')->unique();          // fila de la planilla
      $table->date('fecha')->nullable()->index();          // F
      $table->string('hora', 10)->nullable();             // H
      $table->unsignedSmallInteger('molino_codigo')->nullable()->index();   // M (1001..1008)
      $table->string('pedido', 12)->nullable()->index();  // P
      $table->string('ra', 12)->nullable();               // RA
      $table->unsignedSmallInteger('cliente_numero')->nullable()->index();  // C (1..60)
      $table->string('marca', 5)->nullable();             // *
      $table->date('fecha_entrega')->nullable()->index();  // FE
      $table->string('hora_entrega', 10)->nullable();     // HE
      $table->string('orden', 10)->nullable();            // OR (ej. 1d2)
      $table->string('planta', 5)->nullable();            // PL
      $table->string('calidad', 10)->nullable();          // CA
      $table->string('tolvas', 10)->nullable();           // T
      $table->string('bolsas', 10)->nullable();           // B
      $table->string('precio', 15)->nullable();           // P (precio)
      $table->string('accion', 10)->nullable()->index();  // D: PedN, ConfF, CambF, SuspF
      $table->text('observaciones')->nullable();          // O
      $table->json('original')->nullable();               // valores originales que no se pudieron interpretar (fechas)
      $table->timestamps();
    });

    Schema::create('exp_historial_control', function (Blueprint $table) {
      $table->id();
      $table->unsignedInteger('fila')->unique();
      $table->date('fecha')->nullable()->index();          // FE
      $table->unsignedSmallInteger('molino_codigo')->nullable()->index();   // M
      $table->unsignedSmallInteger('cliente_numero')->nullable()->index();  // número al principio de C
      $table->string('detalle', 100)->nullable();         // C completo (ej. "*8 30HC")
      $table->string('transporte', 100)->nullable();      // T.
      $table->string('hora_carga', 255)->nullable();      // H.C
      $table->string('estado', 255)->nullable();          // O
      $table->string('confirmado', 10)->nullable();       // columna sin título (OK / X / ?)
      $table->string('kg_molino', 100)->nullable();       // Mol.
      $table->string('kg_cliente', 150)->nullable();      // Cte.
      $table->string('kg_diferencia', 100)->nullable();   // Dif.
      $table->string('remito', 50)->nullable();           // Rto.
      $table->string('otro', 50)->nullable();             // Cte (última columna)
      $table->json('original')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exp_historial_diaria');
    Schema::dropIfExists('exp_historial_control');
  }
};
