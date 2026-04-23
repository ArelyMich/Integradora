<?php

namespace App\Mail;

use App\Models\Secuencia;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DictamenCorrecionesMail extends Mailable
{
    use Queueable, SerializesModels;

    public Secuencia $secuencia;
    public User $docente;
    public User $revisor;
    public string $motivo;
    public string $nombreDocente;
    public Collection $comentariosPendientes;

    /**
     * Create a new message instance.
     */
    public function __construct(Secuencia $secuencia, User $docente, User $revisor, string $motivo, Collection $comentariosPendientes)
    {
        $this->secuencia = $secuencia;
        $this->docente = $docente;
        $this->revisor = $revisor;
        $this->motivo = $motivo;
        $this->comentariosPendientes = $comentariosPendientes;
        
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
            from: env('MAIL_FROM'),
            subject: '⚠️ Dictamen de Correcciones - ' . $this->secuencia->materia?->nombre,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.dictamen-correcciones',
            with: [
                'secuencia' => $this->secuencia,
                'docente' => $this->docente,
                'revisor' => $this->revisor,
                'motivo' => $this->motivo,
                'nombreDocente' => $this->nombreDocente,
                'comentariosPendientes' => $this->comentariosPendientes,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
