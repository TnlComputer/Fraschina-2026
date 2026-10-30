<?php

use App\Models\AuxCargos;
use Illuminate\Database\Migrations\Migration;

/**
 * "Jefe de Abastecimiento" tenía la categoría 5 (la de Presidente) y en el personal aparecía
 * a la altura del Presidente. Pasa a la 33, libre dentro de la franja de los Jefes (30-43).
 */
return new class extends Migration
{
  public function up(): void
  {
    $cargo = AuxCargos::withTrashed()->where('cargo', 'Jefe de Abastecimiento')->where('categoria_id', 5)->first();
    $cargo?->update(['categoria_id' => 33]); // el cambio queda en la auditoría
  }

  public function down(): void
  {
    // Queda en la auditoría; no se revierte automáticamente.
  }
};
