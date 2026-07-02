<?php

namespace App\Jobs;

use App\Models\SmsLog;
use App\Services\NghCorpService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendContributionSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries  = 3;
    public int $backoff = 60;

    public function __construct(
        private readonly string $phone,
        private readonly string $memberName,
        private readonly float  $amount,
        private readonly string $reference,
        private readonly string $organizationName,
        private readonly int    $organizationId,
    ) {}

    public function handle(NghCorpService $termii): void
    {
        $sent = $termii->sendContributionConfirmation(
            $this->phone,
            $this->memberName,
            $this->amount,
            $this->reference,
            $this->organizationName,
        );

        // Persister dans sms_logs
        $formattedAmount = number_format($this->amount, 0, ',', ' ');
        $message = "{$this->organizationName} : Bonjour {$this->memberName}, votre cotisation"
                 . " de {$formattedAmount} FCFA pour aujourd'hui a bien ete enregistree."
                 . " Ref: {$this->reference}.";

        SmsLog::create([
            'organization_id'  => $this->organizationId,
            'recipient'        => $this->phone,
            'message'          => $message,
            'type'             => 'confirmation',
            'status'           => $sent ? 'sent' : 'failed',
            'response_payload' => null,
        ]);

        if (! $sent) {
            Log::warning('SMS confirmation cotisation non envoyé', [
                'phone'     => $this->phone,
                'reference' => $this->reference,
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Échec définitif SMS confirmation cotisation', [
            'phone'     => $this->phone,
            'reference' => $this->reference,
            'error'     => $exception->getMessage(),
        ]);
    }
}
