<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Email;

class ResendTransport extends AbstractTransport
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        parent::__construct();
        $this->apiKey = $apiKey;
    }

    protected function doSend(SentMessage $message): void
    {
        $email = $message->getOriginalMessage();

        if (!$email instanceof Email) {
            return;
        }

        $from = $email->getFrom();
        $fromAddress = !empty($from) ? $from[0]->getAddress() : 'noreply@resend.dev';

        $recipients = $this->getRecipients($email);

        if (empty($recipients)) {
            return;
        }

        try {
            $this->sendViaResendApi(
                $fromAddress,
                $recipients,
                $email->getSubject(),
                $email->getHtmlBody(),
                $email->getTextBody()
            );
        } catch (\Exception $e) {
            \Log::error('Resend email error: ' . $e->getMessage());
        }
    }

    private function sendViaResendApi(
        string $from,
        array $to,
        ?string $subject,
        ?string $html,
        ?string $text
    ): void {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.resend.com/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ]);

        $payload = [
            'from' => $from,
            'to' => $to,
            'subject' => $subject ?? 'Sin asunto',
        ];

        if ($html) {
            $payload['html'] = $html;
        }

        if ($text) {
            $payload['text'] = $text;
        }

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new \Exception('Resend API error: ' . $response);
        }
    }

    public function __toString(): string
    {
        return 'resend';
    }

    /**
     * Get recipients from email.
     */
    protected function getRecipients(Email $email): array
    {
        $recipients = [];

        foreach ($email->getTo() as $address) {
            $recipients[] = $address->getAddress();
        }

        foreach ($email->getCc() as $address) {
            $recipients[] = $address->getAddress();
        }

        foreach ($email->getBcc() as $address) {
            $recipients[] = $address->getAddress();
        }

        return $recipients;
    }
}
