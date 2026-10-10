<?php

namespace App\Console\Commands;

use App\Mail\ConsultaMolinoMail;
use App\Models\ExpPedido;
use App\Models\ExpPedidoLinea;
use App\Services\Notificador;
use Illuminate\Console\Command;

/**
 * Consultas automáticas a los molinos:
 * - manana: entregas del día.
 * - tarde: cargas para las entregas del día siguiente.
 * Solo a los destinatarios con el evento correspondiente activo.
 */
class ExpedicionConsultas extends Command
{
  protected $signature = 'expedicion:consultas {turno : manana o tarde}';

  protected $description = 'Envía a cada molino la consulta de entregas del día (mañana) o de cargas del día siguiente (tarde)';

  public function handle(Notificador $notificador): int
  {
    $turno = $this->argument('turno');
    if (!in_array($turno, ['manana', 'tarde'])) {
      $this->error('El turno debe ser "manana" o "tarde".');
      return self::INVALID;
    }

    $fecha = $turno === 'manana' ? now() : now()->addDay();
    $evento = $turno === 'manana' ? 'consulta_manana' : 'consulta_tarde';

    $porMolino = ExpPedidoLinea::with(['pedido.cliente', 'pedido.molino'])
      ->whereDate('fecha_entrega', $fecha->toDateString())
      ->whereHas('pedido', fn($q) => $q->vigentes()->whereIn('estado', [ExpPedido::CONFIRMADO, ExpPedido::PENDIENTE_CIERRE]))
      ->orderBy('hora')
      ->get()
      ->groupBy(fn($l) => $l->pedido->expedicion_molino_id);

    $enviadas = 0;
    foreach ($porMolino as $lineas) {
      $molino = $lineas->first()->pedido->molino;
      // Solo destinatarios del propio molino (no los generales)
      $destinatarios = $notificador->destinatarios($evento, $molino->id)
        ->where('expedicion_molino_id', $molino->id);
      if ($destinatarios->isEmpty()) {
        continue;
      }

      $texto = "Fraschina - {$fecha->format('d/m')}: " . $lineas->map(fn($l) => "{$l->pedido->codigo} {$l->pedido->cliente->sigla} {$l->calidad} {$l->envase}")->implode('; ');
      $notificador->enviar($destinatarios, new ConsultaMolinoMail($molino, $lineas, $turno, $fecha->format('d/m/Y')), $texto);
      $enviadas++;
    }

    $this->info("Consultas enviadas: {$enviadas}");

    return self::SUCCESS;
  }
}
