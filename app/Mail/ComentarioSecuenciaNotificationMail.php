<?php

namespace App\Mail;

use App\Models\SecuenciaComentario;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
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

    /**
     * Create a new message instance.
     */
    public function __construct(SecuenciaComentario $comentario, User $docente, User $comentador)
    {
        $this->comentario = $comentario;
        $this->docente = $docente;
        $this->comentador = $comentador;
        
        // Construir nombre completo del docente
        $nombre = trim($docente->name ?? '');
        $apellido_paterno = trim($docente->apellido_paterno ?? '');
        $apellido_materno = trim($docente->apellido_materno ?? '');
        
        $this->nombreDocente = implode(' ', array_filter([$nombre, $apellido_paterno, $apellido_materno]));
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo Comentario en tu Secuencia Didáctica',
        );
    }

    /**
     * Get the message content definition.
     */
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

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
