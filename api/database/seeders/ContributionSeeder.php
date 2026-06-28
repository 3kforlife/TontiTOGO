<?php

namespace Database\Seeders;

use App\Models\Contribution;
use App\Models\Organization;
use App\Models\TontineParticipant;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContributionSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('name', 'Tontine Solidaire de Lomé')->firstOrFail();
        $agents = User::where('organization_id', $organization->id)
            ->where('role', 'agent')
            ->where('status', 'active')
            ->orderBy('id')
            ->get();
        $participants = TontineParticipant::whereHas(
            'tontine',
            fn ($query) => $query->where('organization_id', $organization->id)
        )->with('tontine')->orderBy('id')->get();

        foreach (range(0, 89) as $index) {
            $participant = $participants[$index % $participants->count()];
            $agent = $agents[$index % $agents->count()];
            $collectedAt = now()
                ->subDays($index % 60)
                ->setTime(8 + ($index % 9), ($index * 7) % 60);
            $reference = sprintf(
                'CTR-%s-%05d',
                $collectedAt->format('Ymd'),
                $index + 1
            );

            $contribution = Contribution::updateOrCreate(
                ['reference' => $reference],
                [
                    'tontine_participant_id' => $participant->id,
                    'user_id' => $agent->id,
                    'amount' => $participant->chosen_amount,
                    'latitude' => 6.1250 + (($index % 20) / 1000),
                    'longitude' => 1.2050 + (($index % 20) / 1000),
                    'settlement_status' => $index < 18 ? 'pending' : 'settled',
                ]
            );

            $contribution->timestamps = false;
            $contribution->created_at = $collectedAt;
            $contribution->updated_at = $collectedAt;
            $contribution->save();
        }
    }
}
