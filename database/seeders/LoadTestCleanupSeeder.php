<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class LoadTestCleanupSeeder extends Seeder
{
    public function run(): void
    {
        $ids = DB::table('users')->where('email', 'like', '%@loadtest.test')->pluck('id');

        DB::transaction(function () use ($ids) {
            foreach ($ids->chunk(500) as $potongan) {
                $daftar = $potongan->all();

                DB::table('transactions')->whereIn('buyer_id', $daftar)->delete();

                if (Schema::hasTable('sessions')) {
                    DB::table('sessions')->whereIn('user_id', $daftar)->delete();
                }

                DB::table('users')->whereIn('id', $daftar)->delete();
            }

            if (Schema::hasTable('password_reset_tokens')) {
                DB::table('password_reset_tokens')->where('email', 'like', '%@loadtest.test')->delete();
            }

            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->whereNull('user_id')->delete();
            }
        });

        File::delete([
            storage_path('app/loadtest/pembeli.csv'),
            storage_path('app/loadtest/konten.csv'),
        ]);

        $this->command->info("Pembersihan selesai: {$ids->count()} akun uji dihapus.");
    }
}
