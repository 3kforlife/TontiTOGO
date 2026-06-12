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
        $org     = Organization::first();
        $members = Member::all();

        // Tontine journalière
        $tontineDaily = Tontine::create([
            'organization_id' => $org->id,
            'name'            => 'Tontine Journalière Marché',
            'minimum_amount'  => 500,
            'frequency'       => 'daily',
            'start_date'      => now()->startOfYear(),
            'end_date'        => now()->endOfYear(),
            'status'          => 'active',
        ]);

        // Tontine mensuelle
        $tontineMonthly = Tontine::create([
            'organization_id' => $org->id,
            'name'            => 'Tontine Mensuelle Commerçants',
            'minimum_amount'  => 5000,
            'frequency'       => 'monthly',
            'start_date'      => now()->startOfYear(),
            'end_date'        => null,
            'status'          => 'active',
        ]);

        // Inscrire tous les membres dans la tontine journalière
        foreach ($members as $member) {
            TontineParticipant::create([
                'tontine_id'    => $tontineDaily->id,
                'member_id'     => $member->id,
                'chosen_amount' => 500,
                'joined_at'     => now()->startOfYear(),
                'status'        => 'active',
            ]);
        }

        // Inscrire les 4 premiers membres dans la tontine mensuelle
        foreach ($members->take(4) as $member) {
            TontineParticipant::create([
                'tontine_id'    => $tontineMonthly->id,
                'member_id'     => $member->id,
                'chosen_amount' => 10000,
                'joined_at'     => now()->startOfYear(),
                'status'        => 'active',
            ]);
        }

        $this->command->info('✅ 2 tontines créées avec participants.');
    }
}
