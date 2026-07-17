<?php

namespace App\Jobs;

use App\Models\Organization;
use App\Models\SmsLog;
use App\Models\TontineParticipant;
use App\Services\NghCorpService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Tontine;


class SendReminderSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $backoff = 120;

    public function __construct(
        private readonly int $organizationId
    ) {}

    public function handle(NghCorpService $termii): void
    {
        $organization = Organization::find($this->organizationId);

        if (! $organization) {
            Log::warning('SendReminderSmsJob: organisation introuvable', [
                'organization_id' => $this->organizationId,
            ]);
            return;
        }

        $orgName = $organization->name;

        $participants = TontineParticipant::where('status', \App\Enums\ParticipantStatus::Active->value)
            ->whereHas('tontine', fn($q) =>
                $q->where('organization_id', $this->organizationId)
                  ->where('status', \App\Enums\TontineStatus::Active->value)
            )
            ->with(['member:id,firstname,lastname,phone,status', 'tontine:id,name,frequency'])
            ->get();

        $remindersSent = 0;

        $tontine = Tontine::where('organization_id', $this->organizationId)->active()->first();

        $tontineName = $tontine?->name;
        foreach ($participants as $participant) {
            if (! $participant->member || $participant->member->status?->value !== 'active') {
                continue;
            }

            if (! $this->isMemberLate($participant)) {
                continue;
            }

            $phone      = $participant->member->phone;
            $memberName = $participant->member->full_name;

            // Envoyer le SMS via Termii avec template fixe
            $sent = $termii->sendReminderToMember($phone, $memberName, $orgName);

            // Message généré pour le log
            $messageSent = "{$orgName} Bonjour {$memberName}, nous vous rappelons"
                         . " que votre cotisation du jour est attendue. Merci.";

            // Persister dans sms_logs
            SmsLog::create([
                'organization_id'  => $this->organizationId,
                'recipient'        => $phone,
                'message'          => $messageSent,
                'type'             => 'reminder',
                'status'           => $sent ? 'sent' : 'failed',
                'response_payload' => null,
            ]);

            if ($sent) {
                $remindersSent++;
            } else {
                Log::warning('SendReminderSmsJob: SMS rappel non envoyé', [
                    'phone'       => $phone,
                    'member'      => $memberName,
                    'tontine'     => $tontineName,
                    'org_id'      => $this->organizationId,
                ]);
            }
        }

        Log::info('SendReminderSmsJob terminé', [
            'organization_id' => $this->organizationId,
            'reminders_sent'  => $remindersSent,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendReminderSmsJob: échec définitif', [
            'organization_id' => $this->organizationId,
            'error'           => $exception->getMessage(),
        ]);
    }

    // -------------------------------------------------------
    // Helpers privés
    // -------------------------------------------------------

    /**
     * Détermine si un participant est en retard selon la fréquence de sa tontine.
     */
    private function isMemberLate(TontineParticipant $participant): bool
    {
        $frequency = $participant->tontine->frequency;

        // Extraire la valeur string si c'est un Enum (PostgreSQL)
        $freqValue = is_object($frequency) ? $frequency->value : $frequency;

        $hasContribution = match ($freqValue) {
            'daily'   => $participant->contributions()
                            ->whereDate('created_at', today())
                            ->exists(),
            'weekly'  => $participant->contributions()
                            ->whereBetween('created_at', [
                                now()->startOfWeek(),
                                now()->endOfWeek(),
                            ])
                            ->exists(),
            'monthly' => $participant->contributions()
                            ->whereYear('created_at', now()->year)
                            ->whereMonth('created_at', now()->month)
                            ->exists(),
            default   => true, // fréquence inconnue → on ne rappelle pas
        };

        return ! $hasContribution;
    }
}
