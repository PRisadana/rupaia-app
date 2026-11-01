<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('rupaia.seed.admin_email');

        if (DB::table('users')->where('email', $email)->exists()) {
            $this->command->info("Akun admin {$email} sudah ada, dilewati.");

            return;
        }

        $password = config('rupaia.seed.admin_password') ?: Str::random(16);

        DB::table('users')->insert([
            'name' => config('rupaia.seed.admin_name'),
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("Akun admin {$email} berhasil dibuat.");

        if (! config('rupaia.seed.admin_password')) {
            $this->command->warn("Password acak (catat sekarang, tidak ditampilkan lagi): {$password}");
        }
    }
}
