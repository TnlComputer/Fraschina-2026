<?php

namespace App\Services;

use App\Models\ExpEntrega;
use App\Models\ExpPedido;
use App\Models\ExpPedidoLinea;
use App\Models\ExpPedidoMovimiento;
use Illuminate\Support\Facades\DB;

/**
 * Reglas de la Central de Pedidos:
 *
 * - Un pedido nace "pendiente de confirmación" (sin número) o directamente confirmado.
 * - Al confirmarse recibe el siguiente número correlativo, con revisión 0.
 * - Si un pedido ya enviado al molino se modifica, se crea la revisión siguiente
 *   (mismo número, -1, -2...) y la anterior queda como histórico. Si todavía no se
 *   envió, se corrige en el lugar.
 * - Cada operación queda registrada en el historial (Diaria).
 */
class CentralPedidos
{
  /**
   * @param array $datos  expedicion_cliente_id, expedicion_molino_id, tipo, fecha_pedido, observaciones
   * @param array $lineas lista de arrays con los campos de ExpPedidoLinea::CAMPOS
   */
  public function crear(array $datos, array $lineas, bool $confirmar, ?int $userId): ExpPedido
  {
    return DB::transaction(function () use ($datos, $lineas, $confirmar, $userId) {
      $pedido = ExpPedido::create([
        'expedicion_cliente_id' => $datos['expedicion_cliente_id'],
        'expedicion_molino_id' => $datos['expedicion_molino_id'],
        'tipo' => $datos['tipo'] ?? 'normal',
        'fecha_pedido' => $datos['fecha_pedido'] ?? now()->toDateString(),
        'observaciones' => $datos['observaciones'] ?? null,
        'estado' => ExpPedido::PENDIENTE_CONFIRMACION,
        'user_id' => $userId,
      ]);

      $this->guardarLineas($pedido, $lineas);
      $this->registrar($pedido, 'creado', $this->resumen($pedido), $userId);

      if ($confirmar) {
        $this->asignarNumero($pedido, $userId);
      }

      return $pedido->fresh(['lineas']);
    });
  }

  /**
   * Modifica un pedido. Devuelve el pedido vigente resultante (puede ser una revisión nueva).
   */
  public function actualizar(ExpPedido $pedido, array $datos, array $lineas, ?int $userId): ExpPedido
  {
    abort_unless($pedido->esEditable(), 422, 'El pedido no se puede modificar.');

    return DB::transaction(function () use ($pedido, $datos, $lineas, $userId) {
      $pedido = ExpPedido::lockForUpdate()->findOrFail($pedido->id);
      $antes = $this->resumen($pedido->load('lineas'));

      if ($pedido->enviado_at && $pedido->numero) {
        $pedido = $this->nuevaRevision($pedido, $userId);
      }

      $pedido->fill([
        'expedicion_cliente_id' => $datos['expedicion_cliente_id'] ?? $pedido->expedicion_cliente_id,
        'expedicion_molino_id' => $datos['expedicion_molino_id'] ?? $pedido->expedicion_molino_id,
        'tipo' => $datos['tipo'] ?? $pedido->tipo,
        'fecha_pedido' => $datos['fecha_pedido'] ?? $pedido->fecha_pedido,
        'observaciones' => $datos['observaciones'] ?? null,
      ])->save();

      $this->guardarLineas($pedido, $lineas);

      $despues = $this->resumen($pedido->load('lineas'));
      $this->registrar($pedido, 'modificado', "Antes: {$antes}\nDespués: {$despues}", $userId);

      return $pedido->fresh(['lineas']);
    });
  }

  public function confirmar(ExpPedido $pedido, ?int $userId): ExpPedido
  {
    abort_unless($pedido->esPendiente() && $pedido->vigente, 422, 'Solo se confirman pedidos pendientes.');

    return DB::transaction(function () use ($pedido, $userId) {
      $pedido = ExpPedido::lockForUpdate()->findOrFail($pedido->id);
      $this->asignarNumero($pedido, $userId);
      return $pedido->fresh();
    });
  }

  public function cambiarEstado(ExpPedido $pedido, string $estado, ?int $userId, ?string $motivo = null): ExpPedido
  {
    abort_unless(array_key_exists($estado, ExpPedido::ESTADOS), 422, 'Estado inválido.');
    abort_if($estado === ExpPedido::PENDIENTE_CONFIRMACION, 422, 'Un pedido no vuelve a pendiente de confirmación.');
    abort_unless($pedido->vigente, 422, 'Solo se cambia el estado de la revisión vigente.');

    if ($estado === ExpPedido::CONFIRMADO && $pedido->esPendiente()) {
      return $this->confirmar($pedido, $userId);
    }

    return DB::transaction(function () use ($pedido, $estado, $userId, $motivo) {
      $anterior = $pedido->estado_nombre;
      $pedido->update(['estado' => $estado]);
      $accion = $estado === ExpPedido::ANULADO ? 'anulado' : 'estado';
      $this->registrar($pedido, $accion, trim("{$anterior} → {$pedido->estado_nombre}. " . ($motivo ?? '')), $userId);
      return $pedido;
    });
  }

  public function registrarEnvio(ExpPedido $pedido, string $archivo, string $destinatarios, ?int $userId): void
  {
    $pedido->update(['archivo' => $archivo, 'enviado_at' => now()]);
    $this->registrar($pedido, 'enviado', "Archivo {$archivo}. Destinatarios: " . ($destinatarios ?: 'ninguno'), $userId);
  }

  public function registrar(ExpPedido $pedido, string $accion, ?string $detalle, ?int $userId): void
  {
    ExpPedidoMovimiento::create([
      'pedido_id' => $pedido->id,
      'numero' => $pedido->numero,
      'revision' => $pedido->revision ?? 0,
      'accion' => $accion,
      'detalle' => $detalle,
      'user_id' => $userId,
    ]);
  }

  /**
   * Siguiente número correlativo. Debe llamarse dentro de una transacción.
   */
  public function siguienteNumero(): int
  {
    $max = ExpPedido::whereNotNull('numero')->lockForUpdate()->max('numero');
    return max((int) $max + 1, (int) config('expedicion.numero_inicial', 1));
  }

  /**
   * Resumen legible de un pedido para el historial.
   */
  public function resumen(ExpPedido $pedido): string
  {
    $lineas = $pedido->lineas->map(function (ExpPedidoLinea $l) {
      $partes = array_filter([
        $l->calidad,
        $l->envase,
        $l->precio !== null ? '$' . rtrim(rtrim(number_format((float) $l->precio, 3, ',', '.'), '0'), ',') : null,
        $l->condicion,
        $l->planta ? 'planta ' . $l->planta : null,
        $l->fecha_entrega ? 'entrega ' . $l->fecha_entrega->format('d/m/Y') . ($l->fecha_tentativa ? ' (tentativa)' : '') : 'fecha a confirmar',
        $l->plazo_dias !== null ? $l->plazo_dias . ' días' : null,
        $l->forma_pago,
      ]);
      return '#' . $l->linea . ' ' . implode(', ', $partes);
    })->implode(' | ');

    return trim(($pedido->tipo === 'muestra' ? '[Muestra sin cargo] ' : '') . $lineas);
  }

  private function asignarNumero(ExpPedido $pedido, ?int $userId): void
  {
    $pedido->update([
      'numero' => $this->siguienteNumero(),
      'revision' => 0,
      'estado' => ExpPedido::CONFIRMADO,
    ]);
    $this->registrar($pedido, 'confirmado', 'Se asigna el número ' . $pedido->codigo, $userId);
  }

  /**
   * Copia el pedido como revisión siguiente; la actual deja de ser vigente.
   * Las entregas ya cargadas pasan a las líneas equivalentes de la revisión nueva.
   */
  private function nuevaRevision(ExpPedido $actual, ?int $userId): ExpPedido
  {
    $actual->update(['vigente' => false]);

    $nueva = $actual->replicate(['archivo', 'enviado_at']);
    $nueva->revision = $actual->revision + 1;
    $nueva->vigente = true;
    $nueva->user_id = $userId ?? $actual->user_id;
    $nueva->save();

    foreach ($actual->lineas as $linea) {
      $copia = $nueva->lineas()->create($linea->only(ExpPedidoLinea::CAMPOS));
      ExpEntrega::where('pedido_linea_id', $linea->id)->update(['pedido_linea_id' => $copia->id]);
    }

    $this->registrar($nueva, 'revision', 'Reemplaza a ' . $actual->codigo, $userId);

    return $nueva->load('lineas');
  }

  /**
   * Reemplaza las líneas del pedido conservando las entregas de las líneas que siguen (por número de línea).
   */
  private function guardarLineas(ExpPedido $pedido, array $lineas): void
  {
    $existentes = $pedido->lineas()->get()->keyBy('linea');
    $conservadas = [];

    foreach (array_values($lineas) as $i => $datos) {
      $numero = $i + 1;
      $valores = array_merge(
        array_fill_keys(ExpPedidoLinea::CAMPOS, null),
        ['tolvas' => 0, 'bolsas' => 0, 'fecha_tentativa' => false],
        array_intersect_key($datos, array_flip(ExpPedidoLinea::CAMPOS)),
        ['linea' => $numero]
      );
      $valores['tolvas'] = (int) ($valores['tolvas'] ?? 0);
      $valores['bolsas'] = (int) ($valores['bolsas'] ?? 0);
      $valores['fecha_tentativa'] = (bool) ($valores['fecha_tentativa'] ?? false);

      if ($linea = $existentes->get($numero)) {
        $linea->update($valores);
      } else {
        $pedido->lineas()->create($valores);
      }
      $conservadas[] = $numero;
    }

    ExpPedidoLinea::where('pedido_id', $pedido->id)->whereNotIn('linea', $conservadas ?: [0])->delete();
    $pedido->unsetRelation('lineas');
  }
}
