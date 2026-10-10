<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ExpPedido extends Model
{
  public const PENDIENTE_CONFIRMACION = 'pendiente_confirmacion';
  public const CONFIRMADO = 'confirmado';
  public const PENDIENTE_CIERRE = 'pendiente_cierre';
  public const CERRADO = 'cerrado';
  public const ANULADO = 'anulado';

  public const ESTADOS = [
    self::PENDIENTE_CONFIRMACION => 'Pendiente de confirmación',
    self::CONFIRMADO => 'Confirmado',
    self::PENDIENTE_CIERRE => 'Pendiente de cierre',
    self::CERRADO => 'Cerrado',
    self::ANULADO => 'Anulado',
  ];

  public const TIPOS = [
    'normal' => 'Normal',
    'muestra' => 'Muestra sin cargo',
  ];

  protected $table = 'exp_pedidos';

  protected $guarded = [];

  protected $attributes = [
    'revision' => 0,
    'vigente' => true,
    'estado' => self::PENDIENTE_CONFIRMACION,
    'tipo' => 'normal',
  ];

  protected $casts = [
    'vigente' => 'boolean',
    'fecha_pedido' => 'date',
    'enviado_at' => 'datetime',
  ];

  public function cliente()
  {
    return $this->belongsTo(ExpedicionCliente::class, 'expedicion_cliente_id')->withTrashed();
  }

  public function molino()
  {
    return $this->belongsTo(ExpedicionMolino::class, 'expedicion_molino_id')->withTrashed();
  }

  public function lineas()
  {
    return $this->hasMany(ExpPedidoLinea::class, 'pedido_id')->orderBy('linea');
  }

  public function movimientos()
  {
    return $this->hasMany(ExpPedidoMovimiento::class, 'pedido_id')->latest('id');
  }

  public function usuario()
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  /**
   * Revisiones del mismo número (incluida esta), de la más nueva a la más vieja.
   */
  public function revisiones()
  {
    return static::where('numero', $this->numero)->whereNotNull('numero')->orderByDesc('revision');
  }

  public function scopeVigentes(Builder $query): Builder
  {
    return $query->where('vigente', true);
  }

  /**
   * Número para mostrar: "1234-0", "1234-1"... o "PEND-15" si aún no se confirmó.
   */
  public function getCodigoAttribute(): string
  {
    return $this->numero ? $this->numero . '-' . $this->revision : 'PEND-' . $this->id;
  }

  public function getEstadoNombreAttribute(): string
  {
    return self::ESTADOS[$this->estado] ?? $this->estado;
  }

  public function getTipoNombreAttribute(): string
  {
    return self::TIPOS[$this->tipo] ?? $this->tipo;
  }

  public function esPendiente(): bool
  {
    return $this->estado === self::PENDIENTE_CONFIRMACION;
  }

  public function esEditable(): bool
  {
    return $this->vigente && !in_array($this->estado, [self::CERRADO, self::ANULADO]);
  }

  /**
   * Configuración de la combinación cliente-molino de este pedido.
   */
  public function configuracion(): ?ExpedicionMolinoTextoCliente
  {
    return ExpedicionMolinoTextoCliente::para($this->expedicion_cliente_id, $this->expedicion_molino_id);
  }

  public function totalTolvas(): int
  {
    return (int) $this->lineas->sum('tolvas');
  }

  public function totalBolsas(): int
  {
    return (int) $this->lineas->sum('bolsas');
  }

  /**
   * Nombre en código del archivo de la planilla: {numero}-{revision}_{molino}_{cliente}_{fecha}.pdf
   */
  public function nombreArchivo(): string
  {
    $sigla = fn($s) => preg_replace('/[^A-Za-z0-9]/', '', (string) $s) ?: 'X';
    return sprintf(
      '%s_%s_%s_%s.pdf',
      $this->codigo,
      $sigla($this->molino?->sigla),
      $sigla($this->cliente?->sigla),
      now()->format('Ymd')
    );
  }
}
