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
        $org = Organization::first();

        // Responsable
        User::create([
            'organization_id'     => $org->id,
            'firstname'           => 'Adjoavi',
            'lastname'            => 'KOFFI',
            'email'               => 'responsable@tontitogo.tg',
            'phone'               => '22890000001',
            'role'                => 'responsible',
            'status'              => 'active',
            'password'            => Hash::make('password'),
            'must_change_password'=> false,
            'avatar_url'          => 'https://ui-avatars.com/api/?name=Adjoavi+KOFFI&background=2563EB&color=fff',
        ]);

        // Agent 1
        User::create([
            'organization_id'     => $org->id,
            'firstname'           => 'Kokou',
            'lastname'            => 'MENSAH',
            'email'               => null,
            'phone'               => '22890000002',
            'role'                => 'agent',
            'status'              => 'active',
            'password'            => Hash::make('agent123'),
            'must_change_password'=> false,
            'avatar_url'          => 'https://ui-avatars.com/api/?name=Kokou+MENSAH&background=1E40AF&color=fff',
        ]);

        // Agent 2
        User::create([
            'organization_id'     => $org->id,
            'firstname'           => 'Akpene',
            'lastname'            => 'AGBODJAN',
            'email'               => null,
            'phone'               => '22890000003',
            'role'                => 'agent',
            'status'              => 'active',
            'password'            => Hash::make('agent123'),
            'must_change_password'=> true,
            'avatar_url'          => 'https://ui-avatars.com/api/?name=Akpene+AGBODJAN&background=1E40AF&color=fff',
        ]);

        $this->command->info('✅ Utilisateurs créés :');
        $this->command->line('   Responsable → phone: 22890000001 / pass: password');
        $this->command->line('   Agent 1     → phone: 22890000002 / pass: agent123');
        $this->command->line('   Agent 2     → phone: 22890000003 / pass: agent123 (must change password)');
    }
}
