<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Organization;
use App\Models\Tontine;
use App\Models\TontineParticipant;
use Illuminate\Database\Seeder;

class TontineSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('name', 'Tontine Solidaire de Lomé')->firstOrFail();
        $members = Member::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->orderBy('id')
            ->get();

        $definitions = [
            [
                'name' => 'Tontine Journalière du Marché',
                'minimum_amount' => 500,
                'frequency' => 'daily',
                'members' => $members,
                'chosen_amount' => 1000,
            ],
            [
                'name' => 'Tontine Hebdomadaire des Commerçants',
                'minimum_amount' => 5000,
                'frequency' => 'weekly',
                'members' => $members->take(14),
                'chosen_amount' => 7500,
            ],
            [
                'name' => 'Tontine Mensuelle Projets',
                'minimum_amount' => 20000,
                'frequency' => 'monthly',
                'members' => $members->take(10),
                'chosen_amount' => 25000,
            ],
        ];

        foreach ($definitions as $definition) {
            $tontine = Tontine::updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'name' => $definition['name'],
                ],
                [
                    'minimum_amount' => $definition['minimum_amount'],
                    'frequency' => $definition['frequency'],
                    'start_date' => now()->subMonths(6)->startOfMonth(),
                    'end_date' => now()->addMonths(6)->endOfMonth(),
                    'status' => 'active',
                ]
            );

            foreach ($definition['members'] as $index => $member) {
                TontineParticipant::updateOrCreate(
                    [
                        'tontine_id' => $tontine->id,
                        'member_id' => $member->id,
                    ],
                    [
                        'chosen_amount' => $definition['chosen_amount'] + (($index % 3) * $definition['minimum_amount']),
                        'joined_at' => now()->subMonths(5)->addDays($index),
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}
