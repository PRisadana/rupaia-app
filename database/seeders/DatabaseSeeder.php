<?php

namespace Database\Seeders;

use Illuminate\Database\seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Menjalankan seeder data wajib aplikasi Rupaia.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            LicenseSeeder::class,
            TagSeeder::class,
            PresetSeeder::class,
        ]);
    }
}
