<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Organization;
use App\Models\SmsLog;
use Illuminate\Database\Seeder;

class SmsLogSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('name', 'Tontine Solidaire de Lomé')->firstOrFail();
        $members = Member::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->take(12)
            ->get();

        foreach ($members as $index => $member) {
            $type = $index % 3 === 0 ? 'reminder' : 'confirmation';
            $status = $index % 5 === 0 ? 'failed' : 'sent';
            $message = $type === 'reminder'
                ? "Tontine Solidaire de Lomé : Bonjour {$member->firstname}, votre cotisation est attendue."
                : "Tontine Solidaire de Lomé : Bonjour {$member->firstname}, votre cotisation a été enregistrée.";

            SmsLog::updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'recipient' => $member->phone,
                    'message' => $message,
                ],
                [
                    'type' => $type,
                    'status' => $status,
                    'response_payload' => [
                        'seeded' => true,
                        'provider_status' => $status,
                    ],
                ]
            );
        }
    }
}
