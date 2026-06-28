<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::where('name', 'Tontine Solidaire de Lomé')->firstOrFail();

        $settings = [
            'sms_confirmation_enabled' => '1',
            'sms_reminder_enabled' => '1',
            'reminder_hour' => '18:00',
            'currency' => 'FCFA',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['organization_id' => $organization->id, 'key' => $key],
                ['value' => $value]
            );
        }
    }
}
