<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            OrganizationSeeder::class,
            UserSeeder::class,
            MemberSeeder::class,
            TontineSeeder::class,
            ContributionSeeder::class,
            DailySettlementSeeder::class,
            SettingSeeder::class,
            SmsLogSeeder::class,
        ]);
    }
}
