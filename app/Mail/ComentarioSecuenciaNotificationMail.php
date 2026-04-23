<?php

namespace App\Mail;

use App\Models\SecuenciaComentario;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ComentarioSecuenciaNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public SecuenciaComentario $comentario;
    public User $docente;
    public User $comentador;
    public string $nombreDocente;

    public function __construct(SecuenciaComentario $comentario, User $docente, User $comentador)
    {
        $this->comentario = $comentario;
        $this->docente = $docente;
        $this->comentador = $comentador;

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
            subject: 'Nuevo comentario en tu secuencia didactica - ' . $this->comentador->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.comentario-secuencia',
            with: [
                'comentario' => $this->comentario,
                'docente' => $this->docente,
                'comentador' => $this->comentador,
                'nombreDocente' => $this->nombreDocente,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
