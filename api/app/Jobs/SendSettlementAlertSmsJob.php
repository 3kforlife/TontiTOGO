<?php

namespace App\Jobs;

use App\Services\NghCorpService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSettlementAlertSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(
        private readonly string $responsiblePhone,
        private readonly string $agentName,
        private readonly float  $expected,
        private readonly float  $received,
        private readonly string $date,
    ) {}

    public function handle(NghCorpService $termii): void
    {
        $sent = $termii->sendSettlementDiscrepancyAlert(
            $this->responsiblePhone,
            $this->agentName,
            $this->expected,
            $this->received,
            $this->date
        );

        if (! $sent) {
            Log::warning('SMS alerte écart de règlement non envoyé', [
                'responsible_phone' => $this->responsiblePhone,
                'agent'             => $this->agentName,
                'date'              => $this->date,
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Échec définitif SMS alerte règlement', [
            'phone' => $this->responsiblePhone,
            'error' => $exception->getMessage(),
        ]);
    }
}
