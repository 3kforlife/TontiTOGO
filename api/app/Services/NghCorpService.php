<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service SMS NGH Corp — TontiTOGO
 *
 * Architecture identique à TermiiService.
 * Le Sender ID est déclaré dans .env (NGHCORP_SENDER_ID) et doit
 * être pré-approuvé par NGH Corp dans votre espace client.
 *
 * Endpoints :
 *   POST /api/send-sms      → envoi unitaire
 *   POST /api/send-multiple → envoi en masse
 *   POST /api/balance       → solde du compte
 */
class NghCorpService
{
    private string $apiKey;
    private string $apiSecret;
    private string $senderId;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey    = config('services.nghcorp.api_key');
        $this->apiSecret = config('services.nghcorp.api_secret');
        $this->senderId  = config('services.nghcorp.sender_id');
        $this->baseUrl   = rtrim(config('services.nghcorp.base_url', 'https://extranet.nghcorp.net'), '/');
    }

    // -------------------------------------------------------
    // API publique
    // -------------------------------------------------------

    /**
     * Envoie un SMS unitaire vers un numéro togolais.
     *
     * @param  string  $phone    Numéro (ex: 90123456 ou 22890123456)
     * @param  string  $message  Texte du SMS
     */
    public function send(string $phone, string $message): bool
    {
        $to        = $this->formatPhone($phone);
        $reference = (string) time() . rand(1000, 9999);

        $payload = [
            'api_key'    => $this->apiKey,
            'api_secret' => $this->apiSecret,
            'from'       => $this->senderId,
            'to'         => (int) $to,
            'text'       => $message,
            'reference'  => $reference,
        ];

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->withOptions($this->httpOptions())
                ->post("{$this->baseUrl}/api/send-sms", $payload);

            $body = $response->json();

            if (isset($body['status']) && (int) $body['status'] === 200) {
                Log::info('NGH Corp SMS envoyé', [
                    'to'         => $to,
                    'message_id' => $body['messageid'] ?? null,
                    'credits'    => $body['credits'] ?? null,
                ]);
                return true;
            }

            Log::error('NGH Corp SMS échoué', [
                'to'          => $to,
                'status'      => $body['status'] ?? $response->status(),
                'status_desc' => $body['status_desc'] ?? null,
                'response'    => $body,
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('NGH Corp SMS exception réseau', [
                'to'    => $to,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Envoie un SMS en masse vers plusieurs numéros.
     *
     * @param  string[]  $phones   Tableau de numéros
     * @param  string    $message  Texte commun à tous
     */
    public function sendBulk(array $phones, string $message): bool
    {
        $formatted = array_map(fn(string $p) => $this->formatPhone($p), $phones);

        $payload = [
            'credentials' => [
                'api_key'    => $this->apiKey,
                'api_secret' => $this->apiSecret,
            ],
            'messages' => [
                [
                    'from'    => $this->senderId,
                    'to'      => $formatted,
                    'content' => $message,
                ],
            ],
        ];

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->withOptions($this->httpOptions())
                ->post("{$this->baseUrl}/api/send-multiple", $payload);

            $results = $response->json();

            $allOk = collect((array) $results)->every(
                fn($r) => isset($r['status']) && (int) $r['status'] === 200
            );

            if ($allOk) {
                Log::info('NGH Corp SMS bulk envoyé', ['count' => count($formatted)]);
                return true;
            }

            Log::error('NGH Corp SMS bulk partiellement échoué', ['results' => $results]);
            return false;

        } catch (\Exception $e) {
            Log::error('NGH Corp SMS bulk exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Récupère le solde de crédits du compte NGH Corp.
     */
    public function getBalance(): ?int
    {
        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->withOptions($this->httpOptions())
                ->post("{$this->baseUrl}/api/balance", [
                    'api_key'    => $this->apiKey,
                    'api_secret' => $this->apiSecret,
                ]);

            $body = $response->json();

            if (isset($body['status']) && (int) $body['status'] === 200) {
                return (int) ($body['balance'] ?? 0);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('NGH Corp balance exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // -------------------------------------------------------
    // Messages métier TontiTOGO — Templates FIXES
    // -------------------------------------------------------

    /**
     * SMS de confirmation de cotisation.
     * "[Org] : Bonjour [Membre], votre cotisation de [Montant] FCFA
     *  pour aujourd'hui a bien été enregistrée. Réf: [Reference]."
     */
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

    /**
     * SMS de rappel de cotisation.
     * "[Org] Bonjour [Membre], nous vous rappelons que votre cotisation
     *  du jour est attendue. Merci."
     */
    public function sendReminderToMember(
        string $phone,
        string $memberName,
        string $organizationName = 'TontiTOGO'
    ): bool {
        $message = "{$organizationName} Bonjour {$memberName}, nous vous rappelons"
                 . " que votre cotisation du jour est attendue. Merci.";

        return $this->send($phone, $message);
    }

    /**
     * SMS d'alerte au responsable lors d'un écart de règlement.
     */
    public function sendSettlementDiscrepancyAlert(
        string $phone,
        string $agentName,
        float  $expected,
        float  $received,
        string $date
    ): bool {
        $diff    = number_format(abs($received - $expected), 0, ',', ' ');
        $message = "TontiTOGO ALERTE: Ecart de {$diff} FCFA pour l'agent {$agentName}"
                 . " le {$date}. Attendu: " . number_format($expected, 0, ',', ' ')
                 . " FCFA. Recu: " . number_format($received, 0, ',', ' ') . " FCFA.";

        return $this->send($phone, $message);
    }

    /**
     * SMS d'identifiants pour un nouvel agent.
     */
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

    // -------------------------------------------------------
    // Helpers privés
    // -------------------------------------------------------

    /**
     * Normalise un numéro togolais au format international 228XXXXXXXX.
     */
    private function formatPhone(string $phone): string
    {
        $clean = preg_replace('/\D/', '', $phone);

        if (str_starts_with($clean, '228')) {
            return $clean;
        }

        return '228' . $clean;
    }

    /**
     * Options Guzzle/cURL.
     * NGHCORP_VERIFY_SSL=false uniquement en développement local sur Windows.
     */
    private function httpOptions(): array
    {
        if (app()->isLocal() && ! config('services.nghcorp.verify_ssl', true)) {
            return ['verify' => false];
        }

        $cacertPath = storage_path('app/cacert.pem');
        if (file_exists($cacertPath)) {
            return ['verify' => $cacertPath];
        }

        return [];
    }
}
