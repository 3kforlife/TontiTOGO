<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TermiiService
{
    private string $apiKey;
    private string $senderId;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey   = config('services.termii.api_key');
        $this->senderId = config('services.termii.sender_id');
        $this->baseUrl  = rtrim(config('services.termii.base_url'), '/');
    }

    public function send(string $phone, string $message): bool
    {
        $payload = [
            'api_key' => $this->apiKey,
            'to'      => $this->formatPhone($phone),
            'from'    => $this->senderId,
            'sms'     => $message,
            'type'    => 'plain',  
            'channel' => 'dnd',  
        ];

        try {
            $response = Http::acceptJson()
                ->post("{$this->baseUrl}/api/sms/send", $payload);

            $body = $response->json();

            if ($response->successful() && isset($body['code']) && $body['code'] === 'ok') {
                Log::info('Termii SMS envoyé', [
                    'to'         => $payload['to'],
                    'message_id' => $body['message_id'] ?? null,
                    'balance'    => $body['balance'] ?? null,
                ]);
                return true;
            }

            Log::error('Termii SMS échoué', [
                'to'       => $payload['to'],
                'status'   => $response->status(),
                'response' => $body,
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Termii SMS exception réseau', [
                'to'    => $payload['to'],
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Envoie un SMS en masse (bulk) à plusieurs numéros.
     * Endpoint dédié : /api/sms/send/bulk
     * Le tableau "to" accepte jusqu'à 100 numéros.
     *
     * @param  string[]  $phones   Tableau de numéros
     * @param  string    $message  Texte commun à tous
     */
    public function sendBulk(array $phones, string $message): bool
    {
        $formatted = array_map(fn(string $p) => $this->formatPhone($p), $phones);

        $payload = [
            'api_key' => $this->apiKey,
            'to'      => $formatted,
            'from'    => $this->senderId,
            'sms'     => $message,
            'type'    => 'plain',
            'channel' => 'dnd',
        ];

        try {
            $response = Http::acceptJson()
                ->post("{$this->baseUrl}/api/sms/send/bulk", $payload);

            $body = $response->json();

            if ($response->successful() && isset($body['code']) && $body['code'] === 'ok') {
                Log::info('Termii SMS bulk envoyé', [
                    'count'      => count($formatted),
                    'message_id' => $body['message_id'] ?? null,
                ]);
                return true;
            }

            Log::error('Termii SMS bulk échoué', [
                'status'   => $response->status(),
                'response' => $body,
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Termii SMS bulk exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function sendContributionConfirmation(
        string $phone,
        string $memberName,
        float  $amount,
        string $reference,
        string $organizationName = 'TontiTOGO'
    ): bool {
        $formattedAmount = number_format($amount, 0, ',', ' ');
        $message = "{$organizationName} : Bonjour {$memberName}, votre cotisation"
                 . " de {$formattedAmount} FCFA pour aujourd'hui a bien ete enregistree."
                 . " Ref: {$reference}.";

        return $this->send($phone, $message);
    }


    public function sendReminderToMember(
        string $phone,
        string $memberName,
        string $organizationName = 'TontiTOGO'
    ): bool {
        $message = "{$organizationName} Bonjour {$memberName}, nous vous rappelons"
                 . " que votre cotisation du jour est attendue. Merci.";

        return $this->send($phone, $message);
    }

    public function sendSettlementDiscrepancyAlert(
        string $phone,
        string $agentName,
        float  $expected,
        float  $received,
        string $date
    ): bool {
        $diff = number_format(abs($received - $expected), 0, ',', ' ');
        $message = "TontiTOGO ALERTE: Ecart de {$diff} FCFA pour l'agent {$agentName}"
                 . " le {$date}. Attendu: " . number_format($expected, 0, ',', ' ')
                 . " FCFA. Recu: " . number_format($received, 0, ',', ' ') . " FCFA.";

        return $this->send($phone, $message);
    }


    public function sendAgentCredentials(
        string $phone,
        string $agentName,
        string $tempPassword
    ): bool {
        $message = "TontiTOGO: Bienvenue {$agentName}! Votre compte agent a ete cree."
                 . " Tel: {$phone} / MDP temp: {$tempPassword}"
                 . " Changez votre mot de passe a la 1ere connexion.";

        return $this->send($phone, $message);
    }

    
    private function formatPhone(string $phone): string
    {
        $clean = preg_replace('/\D/', '', $phone);

        if (str_starts_with($clean, '228')) {
            return $clean;
        }

        return '228' . $clean;
    }
}
