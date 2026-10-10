<?php

namespace App\Mail;

use App\Models\ExpedicionMolino;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Consulta diaria a un molino: por la mañana las entregas del día, por la tarde
 * las cargas para las entregas del día siguiente.
 */
class ConsultaMolinoMail extends Mailable
{
  use Queueable, SerializesModels;

  public function __construct(
    public ExpedicionMolino $molino,
    public Collection $lineas,
    public string $turno,
    public string $fecha,
  ) {}

  public function envelope(): Envelope
  {
    $asunto = $this->turno === 'manana' ? 'Entregas del día' : 'Cargas para mañana';

    return new Envelope(
      from: new Address(config('expedicion.mail_remitente'), config('expedicion.mail_remitente_nombre')),
      subject: "{$asunto} {$this->fecha} - Fraschina",
    );
  }

  public function content(): Content
  {
    return new Content(view: 'mail.expedicion.consulta', with: [
      'molino' => $this->molino,
      'lineas' => $this->lineas,
      'turno' => $this->turno,
      'fecha' => $this->fecha,
    ]);
  }
}
