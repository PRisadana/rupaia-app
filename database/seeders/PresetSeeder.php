<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PresetSeeder extends Seeder
{
    public function run(): void
    {
        $assetDir = database_path('seeders/assets/luts');

        $presets = [
            ['Black and White', 'black-and-white.cube', "{$assetDir}/blackWhite.cube", true],
            ['Cinematic', 'cinematic.cube', "{$assetDir}/cinematic.cube", true],
            ['Nature Wildlife', 'nature-wildlife.cube', "{$assetDir}/natureWildlife.cube", true],
            ['Color Boost', 'color-boost.cube', "{$assetDir}/colorBoost.cube", true],
            ['Lifestyle', 'lifestyle.cube', "{$assetDir}/lifestyle.cube", true],
        ];

        for ($i = 1; $i <= 10; $i++) {
            $number = str_pad((string) $i, 2, '0', STR_PAD_LEFT);

            $presets[] = ["BW Series {$number}", "bw-series-{$number}.cube", "{$assetDir}/bw/BW{$i}.cube", false];
            $presets[] = ["Nature Series {$number}", "nature-series-{$number}.cube", "{$assetDir}/nature/NW-{$i}.cube", false];
        }

        $created = 0;

        foreach ($presets as [$name, $fileName, $source, $isActive]) {
            if (! is_file($source)) {
                $this->command->warn("File sumber tidak ditemukan, preset '{$name}' dilewati: {$source}");

                continue;
            }

            $storagePath = "presets/{$fileName}";

            Storage::disk('public')->put($storagePath, file_get_contents($source));

            if (DB::table('presets')->where('preset_name', $name)->exists()) {
                continue;
            }

            DB::table('presets')->insert([
                'preset_name' => $name,
                'preset_file_path' => $storagePath,
                'is_active' => $isActive,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $created++;
        }

        $this->command->info("{$created} preset baru ditambahkan.");
    }
}
