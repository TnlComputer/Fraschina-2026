<?php

namespace App\Console\Commands;

use App\Models\ExpedicionCliente;
use App\Models\ExpedicionMolino;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Carga los molinos (códigos 1001..1008) y los clientes (números 1..60) de las planillas de Expedición.
 *
 * - Molinos: códigos confirmados con la sección Molino (encabezados de "Planif Sem" / "PS Silvia" de Ctrl.xlsx).
 * - Clientes: hoja LISTAS de Ctrl.xlsx. El número coincide con la columna C de Diaria y la hoja de Fichas (verificado).
 * - Se respetan los registros existentes: los ya asociados (confirmado por el usuario el 12/10/2026) reciben su número;
 *   el resto se crea. Nunca se renumeran ids ni se borra nada. Se puede repetir sin duplicar.
 */
class ExpedicionMaestros extends Command
{
  protected $signature = 'expedicion:maestros {ctrl : Ruta a Ctrl.xlsx} {--simular}';

  protected $description = 'Carga molinos y clientes de Expedición con los números de las planillas';

  /** código de planilla => [razón social en la sección Molino, nombre corto de la planilla] */
  private const MOLINOS = [
    1001 => ['Argenpam S.A.', 'Argenpam'],
    1002 => ['Molinos de Alberti S.A.', 'Alberti'],
    1003 => ['Molino FyA Basile S.A.I.C. e I.', 'Basile'],
    1004 => ['Molino Francisco Cores Ltada.', 'Cores'],
    1005 => ['Nuestra Señora de Luján S.H.', 'Luján'],
    1006 => ['Martelletti Hnos. Molino Argentino S.R.L.', 'Martelletti'],
    1007 => ['Moliendas Argentinas S.R.L.', 'Moliendas'],
    1008 => ['Molinos Benvenuto S.A.', 'Benvenuto'],
  ];

  /** Registros que ya existían en la app y corresponden a un número de planilla (confirmado) */
  private const MOLINOS_EXISTENTES = [1005 => 'LU', 1007 => 'MOLIN'];
  private const CLIENTES_EXISTENTES = [1 => 'PERLA', 14 => 'PRODU'];

  public function handle(): int
  {
    $simular = $this->option('simular');
    $resumen = [];

    DB::beginTransaction();
    try {
      // Molinos
      foreach (self::MOLINOS as $codigo => [$razon, $corto]) {
        $molinoId = DB::table('molinos')->where('razonsocial', $razon)->value('id');
        if (!$molinoId) {
          throw new \RuntimeException("No está el molino «{$razon}» en la sección Molino.");
        }
        $existente = ExpedicionMolino::where('codigo', $codigo)->first()
          ?? (isset(self::MOLINOS_EXISTENTES[$codigo]) ? ExpedicionMolino::whereNull('codigo')->where('sigla', self::MOLINOS_EXISTENTES[$codigo])->first() : null);
        $accion = $existente ? ($existente->codigo ? 'ya estaba' : 'asociado') : 'nuevo';
        $molino = $existente ?? new ExpedicionMolino(['sigla' => mb_strtoupper(mb_substr($corto, 0, 5))]);
        $molino->codigo = $codigo;
        $molino->nroMolino_id = $molinoId;   // vínculo con la sección Molino (antes apuntaba a ids inexistentes)
        $molino->save();
        $resumen[] = ['molino', $codigo, $corto, $accion];
      }

      // Clientes
      $libro = IOFactory::createReaderForFile($this->argument('ctrl'))->setReadDataOnly(true)->load($this->argument('ctrl'));
      $hoja = $libro->getSheetByName('LISTAS') ?? throw new \RuntimeException('Ctrl.xlsx no tiene la hoja LISTAS');
      for ($r = 1; $r <= $hoja->getHighestDataRow(); $r++) {
        $numero = $hoja->getCell([2, $r])->getValue();
        $nombre = trim((string) $hoja->getCell([3, $r])->getValue());
        if (!is_numeric($numero) || $nombre === '') {
          continue;
        }
        $numero = (int) $numero;
        $existente = ExpedicionCliente::where('numero', $numero)->first()
          ?? (isset(self::CLIENTES_EXISTENTES[$numero]) ? ExpedicionCliente::whereNull('numero')->where('sigla', self::CLIENTES_EXISTENTES[$numero])->first() : null);
        $accion = $existente ? ($existente->numero ? 'ya estaba' : 'asociado') : 'nuevo';
        $cliente = $existente ?? new ExpedicionCliente(['sigla' => $this->sigla($nombre)]);
        $cliente->numero = $numero;
        $cliente->nombre_planilla = $nombre;
        $cliente->save();
        $resumen[] = ['cliente', $numero, $nombre, $accion];
      }

      $simular ? DB::rollBack() : DB::commit();
    } catch (\Throwable $e) {
      DB::rollBack();
      $this->error($e->getMessage());
      return self::FAILURE;
    }

    $this->table(['Tipo', 'N.º', 'Nombre', 'Acción'], $resumen);
    $cuenta = collect($resumen)->countBy(fn($r) => $r[0] . ' ' . $r[3]);
    $this->info($cuenta->map(fn($n, $k) => "$k: $n")->implode(' · ') . ($simular ? ' (simulación, no se guardó nada)' : ''));
    return self::SUCCESS;
  }

  /** Sigla de 5 caracteres a partir del nombre de la planilla, sin repetir las existentes. */
  private function sigla(string $nombre): string
  {
    $base = mb_substr(preg_replace('/[^A-Z0-9]/u', '', mb_strtoupper(\Illuminate\Support\Str::ascii($nombre))), 0, 5) ?: 'CLI';
    $sigla = $base;
    for ($i = 2; ExpedicionCliente::where('sigla', $sigla)->exists(); $i++) {
      $sigla = mb_substr($base, 0, 5 - strlen((string) $i)) . $i;
    }
    return $sigla;
  }
}
