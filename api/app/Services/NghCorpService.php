<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service SMS NGH Corp — TontiTOGO
 *
 * Documentation : https://extranet.nghcorp.net/api/
 *
 * Endpoints utilisés :
 *   POST /api/send-sms      → envoi unitaire
 *   POST /api/send-multiple → envoi en masse
 *   POST /api/balance       → solde du compte
 *
 * Authentification : api_key + api_secret dans le payload JSON.
 *
 * Sender ID : prend le nom de l'organisation pour chaque envoi.
 *   - Limité à 11 caractères alphanumériques (contrainte opérateur).
 *   - Tronqué et nettoyé automatiquement si nécessaire.
 */
class NghCorpService
{
    private string $apiKey;
    private string $apiSecret;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey    = config('services.nghcorp.api_key');
        $this->apiSecret = config('services.nghcorp.api_secret');
        $this->baseUrl   = rtrim(config('services.nghcorp.base_url', 'https://extranet.nghcorp.net'), '/');
    }

    // -------------------------------------------------------
    // API publique
    // -------------------------------------------------------

    /**
     * Envoie un SMS unitaire.
     *
     * @param  string  $phone            Numéro togolais (ex: 90123456 ou 22890123456)
     * @param  string  $message          Texte du SMS
     * @param  string  $organizationName Sender ID affiché (nom de l'organisation)
     */
    public function send(string $phone, string $message, string $organizationName = 'TontiTOGO'): bool
    {
        $to        = $this->formatPhone($phone);
        $senderId  = $this->formatSenderId($organizationName);
        $reference = (string) time();

        $payload = [
            'api_key'    => $this->apiKey,
            'api_secret' => $this->apiSecret,
            'from'       => $senderId,
            'to'         => (int) $to,
            'text'       => $message,
            'reference'  => $reference,
        ];

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->withOptions($this->httpOptions())
                ->post("{$this->baseUrl}/api/send-sms", $payload);
            
            $body = $response->json();

            // Succès : status 200
            if (isset($body['status']) && (int) $body['status'] === 200) {
                Log::info('NGH Corp SMS envoyé', [
                    'to'         => $to,
                    'sender_id'  => $senderId,
                    'message_id' => $body['messageid'] ?? null,
                    'credits'    => $body['credits'] ?? null,
                ]);
                return true;
            }

            Log::error('NGH Corp SMS échoué', [
                'to'         => $to,
                'status'     => $body['status'] ?? $response->status(),
                'status_desc'=> $body['status_desc'] ?? null,
                'response'   => $body,
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
     * Utilise l'endpoint /api/send-multiple de NGH Corp.
     *
     * @param  string[]  $phones           Tableau de numéros
     * @param  string    $message          Texte commun
     * @param  string    $organizationName Sender ID
     */
    public function sendBulk(array $phones, string $message, string $organizationName = 'TontiTOGO'): bool
    {
        $senderId  = $this->formatSenderId($organizationName);
        $formatted = array_map(fn(string $p) => $this->formatPhone($p), $phones);

        $payload = [
            'credentials' => [
                'api_key'    => $this->apiKey,
                'api_secret' => $this->apiSecret,
            ],
            'messages' => [
                [
                    'from'    => $senderId,
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

            // L'API retourne un tableau de résultats par numéro
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
    public function sendContributionConfirmation (
        string $phone,
        string $memberName,
        float  $amount,
        string $reference,
        string $organizationName = 'TontiTOGO'
    ): bool {
        $formattedAmount = number_format($amount, 0, ',', ' ');
        $message = "Bonjour {$memberName}, votre cotisation"
                 . " de {$formattedAmount} FCFA pour aujourd'hui a bien ete enregistree."
                 . " Ref: {$reference}.";

        return $this->send($phone, $message, $organizationName);
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

        return $this->send($phone, $message, $organizationName);
    }

    /**
     * SMS d'alerte au responsable lors d'un écart de règlement.
     */
    public function sendSettlementDiscrepancyAlert(
        string $phone,
        string $agentName,
        float  $expected,
        float  $received,
        string $date,
        string $organizationName = 'TontiTOGO'
    ): bool {
        $diff    = number_format(abs($received - $expected), 0, ',', ' ');
        $message = "{$organizationName} ALERTE: Ecart de {$diff} FCFA pour l'agent {$agentName}"
                 . " le {$date}. Attendu: " . number_format($expected, 0, ',', ' ')
                 . " FCFA. Recu: " . number_format($received, 0, ',', ' ') . " FCFA.";

        return $this->send($phone, $message, $organizationName);
    }

    /**
     * SMS d'identifiants envoyé à un nouvel agent (si encore utilisé).
     */
    public function sendAgentCredentials(
        string $phone,
        string $agentName,
        string $tempPassword,
        string $organizationName = 'TontiTOGO'
    ): bool {
        $message = "{$organizationName}: Bienvenue {$agentName}! Votre compte agent a ete cree."
                 . " Tel: {$phone} / MDP temp: {$tempPassword}"
                 . " Changez votre mot de passe a la 1ere connexion.";

        return $this->send($phone, $message, $organizationName);
    }

    // -------------------------------------------------------
    // Helpers privés
    // -------------------------------------------------------

    /**
     * Normalise un numéro togolais au format international sans + (228XXXXXXXX).
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
     * Formate le Sender ID pour NGH Corp.
     * Contrainte opérateur : max 11 caractères alphanumériques, pas d'espaces.
     * Ex: "Tontine Solidarité" → "TontineSol"
     */
    private function formatSenderId(string $name): string
    {
        // Supprimer les accents
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);

        // Supprimer tout caractère non alphanumérique
        $clean = preg_replace('/[^A-Za-z0-9]/', '', $clean);

        // Tronquer à 11 caractères
        return substr($clean, 0, 11);
    }

    /**
     * Options Guzzle/cURL pour contourner les problèmes SSL en dev local.
     * Contrôlé via NGHCORP_VERIFY_SSL=false dans .env uniquement en local.
     */
    private function httpOptions(): array
    {
        if (app()->isLocal() && ! config('services.nghcorp.verify_ssl', true)) {
            return ['verify' => false]; // DEV uniquement
        }

        $cacertPath = storage_path('app/cacert.pem');
        if (file_exists($cacertPath)) {
            return ['verify' => $cacertPath];
        }

        return [];
    }
}
