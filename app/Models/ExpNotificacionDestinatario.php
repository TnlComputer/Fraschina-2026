<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpNotificacionDestinatario extends Model
{
  use SoftDeletes;

  public const CANALES = [
    'mail' => 'Mail',
    'whatsapp' => 'WhatsApp',
  ];

  public const EVENTOS = [
    'pedido_nuevo' => 'Pedido nuevo',
    'pedido_modificado' => 'Modificación de pedido',
    'consulta_manana' => 'Consulta mañana (entregas del día)',
    'consulta_tarde' => 'Consulta tarde (cargas del día siguiente)',
  ];

  protected $table = 'exp_notificacion_destinatarios';

  protected $guarded = [];

  protected $casts = [
    'activo' => 'boolean',
  ];

  public function molino()
  {
    return $this->belongsTo(ExpedicionMolino::class, 'expedicion_molino_id')->withTrashed();
  }

  public function cliente()
  {
    return $this->belongsTo(ExpedicionCliente::class, 'expedicion_cliente_id')->withTrashed();
  }
}
