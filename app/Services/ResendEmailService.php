<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ResendEmailService
{
    /**
     * Envía un correo usando Resend
     * 
     * @param string|array $to Email(s) destinatario(s)
     * @param Mailable $mailable Clase Mail de Laravel
     * @param string|null $fromName Nombre del remitente (usa MAIL_FROM_NAME por defecto)
     * @return bool
     */
    public static function enviar($to, Mailable $mailable, ?string $fromName = null): bool
    {
        try {
            // Usar MAIL_FROM_NAME del .env o el proporcionado
            $fromName = $fromName ?? env('MAIL_FROM_NAME', 'Nuvem');
            $fromEmail = env('MAIL_FROM', 'onboarding@resend.dev');

            Mail::mailer('resend')
                ->to($to)
                ->send($mailable);

            return true;
        } catch (\Exception $e) {
            Log::error('Error al enviar correo con Resend: ' . $e->getMessage(), [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Envía un correo simple de texto/HTML usando Resend
     * 
     * @param string $to Email destinatario
     * @param string $subject Asunto del correo
     * @param string $html Contenido HTML del correo
     * @return bool
     */
    public static function enviarHTML(string $to, string $subject, string $html): bool
    {
        try {
            $fromEmail = env('MAIL_FROM', 'onboarding@resend.dev');
            $fromName = env('MAIL_FROM_NAME', 'Nuvem');

            // Usar Guzzle/cURL directamente si es necesario
            $client = new \GuzzleHttp\Client();

            $response = $client->post('https://api.resend.com/emails', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('RESEND_API_KEY'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'from' => $fromEmail,
                    'to' => [$to],
                    'subject' => $subject,
                    'html' => $html,
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            Log::error('Error al enviar correo HTML con Resend: ' . $e->getMessage(), [
                'to' => $to,
                'subject' => $subject,
            ]);

            return false;
        }
    }
}
