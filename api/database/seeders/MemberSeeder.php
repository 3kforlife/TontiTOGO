<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('name', 'Tontine Solidaire de Lomé')->firstOrFail();
        $agents = User::where('organization_id', $organization->id)
            ->where('role', 'agent')
            ->where('status', 'active')
            ->orderBy('id')
            ->get();

        $members = [
            ['Abla', 'TSEKPO', 'F', 'Adawlato'],
            ['Dodzi', 'AGBEKO', 'M', 'Bè'],
            ['Ama', 'KPEGLO', 'F', 'Agoè'],
            ['Sélom', 'AWOKOU', 'M', 'Tokoin'],
            ['Mawuli', 'DOSSOU', 'M', 'Adidogomé'],
            ['Efua', 'ALIDOU', 'F', 'Hédzranawoé'],
            ['Yawa', 'KODJO', 'F', 'Nyékonakpoè'],
            ['Edem', 'AMEWODE', 'M', 'Kégué'],
            ['Akossiwa', 'ADJOVI', 'F', 'Nukafu'],
            ['Kossi', 'GBEASOR', 'M', 'Djidjolé'],
            ['Mariam', 'BATCHANA', 'F', 'Attiégou'],
            ['Kodjo', 'LAWSON', 'M', 'Amoutivé'],
            ['Essi', 'AMEGAN', 'F', 'Ablogamé'],
            ['Komla', 'TCHALLA', 'M', 'Hanoukopé'],
            ['Ayélé', 'DEKPO', 'F', 'Sagbado'],
            ['Sena', 'AKAKPO', 'M', 'Légbassito'],
            ['Dédé', 'APEDO', 'F', 'Klikamé'],
            ['Elom', 'KOUVAHEY', 'M', 'Avédji'],
            ['Adjowa', 'KUMAKO', 'F', 'Kpogan'],
            ['Messan', 'AMOUZOU', 'M', 'Baguida'],
        ];

        foreach ($members as $index => [$firstname, $lastname, $gender, $address]) {
            Member::updateOrCreate(
                ['member_code' => sprintf('MBR-%06d', $index + 1)],
                [
                    'organization_id' => $organization->id,
                    'notebook_number' => sprintf('CAR-%04d', $index + 1),
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'phone' => '2289' . sprintf('%07d', 2000001 + $index),
                    'gender' => $gender,
                    'address' => $address . ', Lomé',
                    'status' => $index === 18 ? 'suspended' : 'active',
                    'created_by_agent_id' => $agents[$index % $agents->count()]->id,
                ]
            );
        }
    }
}
