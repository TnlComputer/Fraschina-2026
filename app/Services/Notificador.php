<?php

namespace App\Services;

use App\Models\ExpNotificacionDestinatario;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Envía avisos a los destinatarios configurados para un evento.
 *
 * Mail: se envía con la configuración de correo de Laravel.
 * WhatsApp: queda registrado en el log hasta definir el proveedor (WhatsApp Business API
 * o un gateway en el "móvil 2"); el destinatario y el texto ya quedan armados.
 */
class Notificador
{
  public function destinatarios(string $evento, ?int $molinoId = null, ?int $clienteId = null): Collection
  {
    return ExpNotificacionDestinatario::where('evento', $evento)
      ->where('activo', true)
      ->where(function ($q) use ($molinoId, $clienteId) {
        $q->when($molinoId, fn($q) => $q->orWhere('expedicion_molino_id', $molinoId))
          ->when($clienteId, fn($q) => $q->orWhere('expedicion_cliente_id', $clienteId))
          ->orWhere(fn($q) => $q->whereNull('expedicion_molino_id')->whereNull('expedicion_cliente_id'));
      })
      ->get();
  }

  /**
   * Envía el mailable a los destinatarios de mail y el texto a los de WhatsApp.
   * Devuelve la lista de destinos notificados.
   */
  public function enviar(Collection $destinatarios, Mailable $mail, string $textoWhatsapp): array
  {
    $enviados = [];

    $mails = $destinatarios->where('canal', 'mail')->pluck('destino')->unique()->values();
    if ($mails->isNotEmpty()) {
      Mail::to($mails->all())->send($mail);
      $enviados = array_merge($enviados, $mails->all());
    }

    foreach ($destinatarios->where('canal', 'whatsapp')->pluck('destino')->unique() as $numero) {
      $this->whatsapp($numero, $textoWhatsapp);
      $enviados[] = 'WhatsApp ' . $numero;
    }

    return $enviados;
  }

  protected function whatsapp(string $numero, string $texto): void
  {
    Log::channel(config('logging.default'))->info('WhatsApp pendiente de proveedor', [
      'numero' => $numero,
      'texto' => $texto,
    ]);
  }
}
