<?php

namespace App\Jobs;

use App\Services\TermiiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAgentCredentialsSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 30;

    public function __construct(
        private readonly string $phone,
        private readonly string $agentName,
        private readonly string $tempPassword,
    ) {}

    public function handle(TermiiService $termii): void
    {
        $sent = $termii->sendAgentCredentials(
            $this->phone,
            $this->agentName,
            $this->tempPassword
        );

        if (! $sent) {
            Log::warning('SMS identifiants agent non envoyé', [
                'phone'      => $this->phone,
                'agent_name' => $this->agentName,
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Échec définitif SMS identifiants agent', [
            'phone' => $this->phone,
            'error' => $exception->getMessage(),
        ]);
    }
}
