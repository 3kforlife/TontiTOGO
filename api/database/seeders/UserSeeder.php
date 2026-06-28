<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('name', 'Tontine Solidaire de Lomé')->firstOrFail();

        $users = [
            [
                'firstname' => 'Adjoavi',
                'lastname' => 'KOFFI',
                'email' => 'responsable@tontitogo.tg',
                'phone' => '22890100001',
                'role' => 'responsible',
                'status' => 'active',
                'password' => 'Responsable@123',
                'must_change_password' => false,
            ],
            [
                'firstname' => 'Kokou',
                'lastname' => 'MENSAH',
                'email' => 'kokou.mensah@tontitogo.tg',
                'phone' => '22890100002',
                'role' => 'agent',
                'status' => 'active',
                'password' => 'Agent@123',
                'must_change_password' => false,
            ],
            [
                'firstname' => 'Akpéné',
                'lastname' => 'AGBODJAN',
                'email' => 'akpene.agbodjan@tontitogo.tg',
                'phone' => '22896100003',
                'role' => 'agent',
                'status' => 'active',
                'password' => 'Agent@123',
                'must_change_password' => false,
            ],
            [
                'firstname' => 'Komlan',
                'lastname' => 'AMEGA',
                'email' => 'komlan.amega@tontitogo.tg',
                'phone' => '22897100004',
                'role' => 'agent',
                'status' => 'suspended',
                'password' => 'Agent@123',
                'must_change_password' => false,
            ],
        ];

        foreach ($users as $data) {
            $password = $data['password'];
            unset($data['password']);

            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    ...$data,
                    'organization_id' => $organization->id,
                    'password' => Hash::make($password),
                    'avatar_url' => sprintf(
                        'https://ui-avatars.com/api/?name=%s+%s&background=166534&color=fff',
                        urlencode($data['firstname']),
                        urlencode($data['lastname'])
                    ),
                ]
            );
        }
    }
}
