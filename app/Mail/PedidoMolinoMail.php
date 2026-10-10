<?php

namespace App\Mail;

use App\Models\ExpPedido;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Envío de la planilla de pedido (o de su modificación) al molino.
 */
class PedidoMolinoMail extends Mailable
{
  use Queueable, SerializesModels;

  public function __construct(public ExpPedido $pedido, public string $rutaArchivo) {}

  public function envelope(): Envelope
  {
    $tipo = $this->pedido->revision > 0 ? 'Modificación de pedido' : 'Pedido';

    return new Envelope(
      from: new Address(config('expedicion.mail_remitente'), config('expedicion.mail_remitente_nombre')),
      subject: "{$tipo} {$this->pedido->codigo} - {$this->pedido->cliente->sigla} - Fraschina",
    );
  }

  public function content(): Content
  {
    return new Content(view: 'mail.expedicion.pedido', with: ['pedido' => $this->pedido]);
  }

  public function attachments(): array
  {
    return [
      Attachment::fromStorageDisk(config('expedicion.disco'), $this->rutaArchivo)
        ->as(basename($this->rutaArchivo))
        ->withMime('application/pdf'),
    ];
  }
}
