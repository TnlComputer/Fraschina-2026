<?php

namespace App\Console\Commands;

use App\Exports\RespaldoExpedicionExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExpedicionRespaldo extends Command
{
  protected $signature = 'expedicion:respaldo';

  protected $description = 'Genera el Excel de respaldo diario de la Central de Pedidos y borra los viejos';

  public function handle(): int
  {
    $disco = config('expedicion.disco');
    $carpeta = trim(config('expedicion.carpeta_respaldos'), '/');
    $ruta = $carpeta . '/expedicion-' . now()->format('Y-m-d') . '.xlsx';

    Excel::store(new RespaldoExpedicionExport(), $ruta, $disco);
    $this->info("Respaldo generado: {$ruta}");

    // Conservar solo los últimos N días
    $limite = now()->subDays(config('expedicion.respaldos_dias'))->format('Y-m-d');
    foreach (Storage::disk($disco)->files($carpeta) as $archivo) {
      if (preg_match('/expedicion-(\d{4}-\d{2}-\d{2})\.xlsx$/', $archivo, $m) && $m[1] < $limite) {
        Storage::disk($disco)->delete($archivo);
      }
    }

    return self::SUCCESS;
  }
}
