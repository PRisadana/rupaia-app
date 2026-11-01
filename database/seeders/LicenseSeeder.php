<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LicenseSeeder extends Seeder
{
    public function run(): void
    {
        $licenses = [
            [
                'code' => 'personal',
                'name' => 'Personal License',
                'description' => 'License for personal, non-commercial use only. Suitable for individual projects, personal collections, references, or non-profit personal usage.',
                'terms' => 'The buyer may use the content for personal purposes only. The content may not be resold, redistributed, sublicensed, claimed as original work, or used in commercial products, advertisements, client projects, merchandise, or business activities.',
            ],
            [
                'code' => 'commercial',
                'name' => 'Commercial License',
                'description' => 'License for commercial use in digital or printed projects, including business, marketing, and client work.',
                'terms' => 'The buyer may use the content for commercial projects such as promotional materials, social media, websites, presentations, packaging, and client work. The buyer may not resell, redistribute, sublicense, or upload the original file as a standalone product or competing digital asset.',
            ],
            [
                'code' => 'editorial',
                'name' => 'Editorial License',
                'description' => 'License for news, educational, commentary, research, documentation, or non-promotional publication purposes.',
                'terms' => 'The buyer may use the content for editorial purposes, including articles, blogs, educational materials, reports, or documentation. The content may not be used for advertising, product endorsement, commercial branding, merchandise, or misleading representation.',
            ],
            [
                'code' => 'extended_commercial',
                'name' => 'Extended Commercial License',
                'description' => 'License for commercial use in merchandise, templates, campaigns, and wider distribution projects.',
                'terms' => 'The buyer may use the content in commercial products, merchandise, templates, campaigns, and wider distribution projects. The buyer may not resell, redistribute, sublicense, or offer the original source file as a standalone digital asset. Ownership and copyright remain with the seller unless otherwise stated.',
            ],
            [
                'code' => 'educational',
                'name' => 'Educational License',
                'description' => 'License for teaching, learning, and academic use in schools, universities, and training institutions without commercial purposes.',
                'terms' => 'The buyer may use the content in teaching materials, presentations, assignments, theses, research papers, and other non-commercial academic works. The content may not be used for advertising, resold, redistributed, sublicensed, or included in products that are sold to the public.',
            ],
            [
                'code' => 'digital_use',
                'name' => 'Digital Use License',
                'description' => 'License for commercial use limited to digital media such as websites, social media, mobile apps, presentations, and online campaigns.',
                'terms' => 'The buyer may use the content in digital media for commercial purposes, including websites, social media, online advertising, mobile apps, and presentations. Use in printed materials, packaging, merchandise, or other physical products is not permitted. The buyer may not resell, redistribute, sublicense, or offer the original file as a standalone digital asset.',
            ],
            [
                'code' => 'print_use',
                'name' => 'Print Use License',
                'description' => 'License for commercial use in printed materials such as brochures, posters, magazines, banners, and packaging, excluding products intended for resale.',
                'terms' => 'The buyer may use the content in printed materials for commercial purposes, including brochures, posters, magazines, banners, and product packaging. Use in merchandise intended for resale, templates, or digital distribution is not permitted. The buyer may not resell, redistribute, sublicense, or offer the original file as a standalone asset.',
            ],
        ];

        $now = now();

        $rows = array_map(fn(array $license) => $license + [
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ], $licenses);

        DB::table('licenses')->insertOrIgnore($rows);

        $this->command->info(count($rows) . ' lisensi diproses.');
    }
}
