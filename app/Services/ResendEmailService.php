<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ResendEmailService
{
    public static function enviar($to, Mailable $mailable, ?string $fromName = null): bool
    {
        try {
            $fromName = $fromName ?? config('mail.from.name', 'Nuvem');
            $fromEmail = config('mail.from.address', 'hello@example.com');

            Mail::mailer('resend')
                ->from($fromEmail, $fromName)
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

    public static function enviarHTML(string $to, string $subject, string $html): bool
    {
        try {
            $fromEmail = config('mail.from.address', 'hello@example.com');
            $fromName = config('mail.from.name', 'Nuvem');

            $client = new \GuzzleHttp\Client();

            $response = $client->post('https://api.resend.com/emails', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('RESEND_API_KEY'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'from' => $fromName . ' <' . $fromEmail . '>',
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
