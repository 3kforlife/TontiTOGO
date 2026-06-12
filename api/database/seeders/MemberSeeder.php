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
        $org   = Organization::first();
        $agent = User::where('role', 'agent')->first();

        $members = [
            ['firstname' => 'Abla',    'lastname' => 'TSEKPO',   'phone' => '22891000001', 'gender' => 'F', 'notebook_number' => 'NB-0001'],
            ['firstname' => 'Dodzi',   'lastname' => 'AGBEKO',   'phone' => '22891000002', 'gender' => 'M', 'notebook_number' => 'NB-0002'],
            ['firstname' => 'Ama',     'lastname' => 'KPEGLO',   'phone' => '22891000003', 'gender' => 'F', 'notebook_number' => 'NB-0003'],
            ['firstname' => 'Selom',   'lastname' => 'AWOKOU',   'phone' => '22891000004', 'gender' => 'M', 'notebook_number' => 'NB-0004'],
            ['firstname' => 'Mawuli',  'lastname' => 'DOSSOU',   'phone' => '22891000005', 'gender' => 'M', 'notebook_number' => 'NB-0005'],
            ['firstname' => 'Efua',    'lastname' => 'ALIDOU',   'phone' => '22891000006', 'gender' => 'F', 'notebook_number' => 'NB-0006'],
            ['firstname' => 'Yawa',    'lastname' => 'KODZO',    'phone' => '22891000007', 'gender' => 'F', 'notebook_number' => 'NB-0007'],
            ['firstname' => 'Edem',    'lastname' => 'AMEWODE',  'phone' => '22891000008', 'gender' => 'M', 'notebook_number' => 'NB-0008'],
        ];

        foreach ($members as $index => $data) {
            Member::create([
                'organization_id'     => $org->id,
                'member_code'         => sprintf('MBR-%06d', $index + 1),
                'notebook_number'     => $data['notebook_number'],
                'firstname'           => $data['firstname'],
                'lastname'            => $data['lastname'],
                'phone'               => $data['phone'],
                'gender'              => $data['gender'],
                'address'             => 'Lomé, Togo',
                'status'              => 'active',
                'created_by_agent_id' => $agent->id,
            ]);
        }

        $this->command->info('✅ 8 membres créés.');
    }
}
