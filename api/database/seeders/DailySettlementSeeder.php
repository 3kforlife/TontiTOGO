<?php

namespace Database\Seeders;

use App\Models\Contribution;
use App\Models\DailySettlement;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class DailySettlementSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('name', 'Tontine Solidaire de Lomé')->firstOrFail();
        $responsible = User::where('organization_id', $organization->id)
            ->where('role', 'responsible')
            ->firstOrFail();
        $agents = User::where('organization_id', $organization->id)
            ->where('role', 'agent')
            ->where('status', 'active')
            ->get();

        foreach ($agents as $agentIndex => $agent) {
            foreach (range(2, 8) as $daysAgo) {
                $date = now()->subDays($daysAgo)->toDateString();
                $expected = (float) Contribution::where('user_id', $agent->id)
                    ->whereDate('created_at', $date)
                    ->sum('amount');

                if ($expected <= 0) {
                    continue;
                }

                $hasDiscrepancy = $agentIndex === 1 && $daysAgo === 3;
                $received = $hasDiscrepancy ? $expected - 500 : $expected;

                DailySettlement::updateOrCreate(
                    [
                        'agent_id' => $agent->id,
                        'date_settled' => $date,
                    ],
                    [
                        'organization_id' => $organization->id,
                        'validated_by_responsible_id' => $responsible->id,
                        'expected_amount' => $expected,
                        'received_amount' => $received,
                        'status' => $hasDiscrepancy ? 'discrepancy' : 'validated',
                        'notes' => $hasDiscrepancy
                            ? 'Écart volontaire pour tester les alertes.'
                            : 'Versement contrôlé et validé.',
                    ]
                );
            }
        }
    }
}
