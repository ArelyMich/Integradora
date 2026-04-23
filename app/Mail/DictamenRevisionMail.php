<?php

namespace App\Mail;

use App\Models\Secuencia;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DictamenRevisionMail extends Mailable
{
    use Queueable, SerializesModels;

    public Secuencia $secuencia;
    public User $docente;
    public User $revisor;
    public string $motivo;
    public string $nombreDocente;

    public function __construct(Secuencia $secuencia, User $docente, User $revisor, string $motivo)
    {
        $this->secuencia = $secuencia;
        $this->docente = $docente;
        $this->revisor = $revisor;
        $this->motivo = $motivo;

        $nombre = trim($docente->name ?? '');
        $apellidoPaterno = trim($docente->apellido_paterno ?? '');
        $apellidoMaterno = trim($docente->apellido_materno ?? '');

        $this->nombreDocente = implode(' ', array_filter([$nombre, $apellidoPaterno, $apellidoMaterno]));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
            subject: 'Dictamen de revision - ' . ($this->secuencia->materia?->nombre ?? 'Secuencia'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.dictamen-revision',
            with: [
                'secuencia' => $this->secuencia,
                'docente' => $this->docente,
                'revisor' => $this->revisor,
                'motivo' => $this->motivo,
                'nombreDocente' => $this->nombreDocente,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
