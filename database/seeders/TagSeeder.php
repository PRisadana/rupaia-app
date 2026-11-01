<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            'view',
            'portrait',
            'mountain',
            'beach',
            'graduations',
            'human interest',
            'cityscape',
            'wedding',
            'picture',

            'landscape',
            'nature',
            'wildlife',
            'sunrise',
            'sunset',
            'golden hour',
            'blue hour',
            'forest',
            'ocean',
            'lake',
            'river',
            'waterfall',
            'volcano',
            'rice field',
            'island',
            'desert',
            'snow',
            'sky',
            'clouds',
            'fog',
            'rain',
            'flower',
            'tree',
            'garden',
            'night',
            'astrophotography',
            'long exposure',

            'animal',
            'bird',
            'pet',

            'urban',
            'street',
            'street photography',
            'architecture',
            'building',
            'bridge',
            'skyline',
            'interior',
            'market',
            'traffic',

            'people',
            'couple',
            'family',
            'children',
            'candid',
            'lifestyle',
            'fashion',
            'model',
            'prewedding',
            'engagement',

            'event',
            'concert',
            'festival',
            'sport',
            'ceremony',
            'culture',
            'tradition',
            'temple',
            'dance',
            'conference',

            'travel',
            'tourism',
            'adventure',
            'hiking',
            'bali',
            'indonesia',

            'food',
            'drink',
            'coffee',
            'product',
            'still life',
            'macro',

            'black and white',
            'monochrome',
            'minimalist',
            'abstract',
            'texture',
            'pattern',
            'background',
            'vintage',
            'cinematic',
            'aerial',
            'drone',
            'silhouette',
            'bokeh',
            'documentary',
        ];

        $created = 0;

        foreach (array_unique($tags) as $name) {
            if (DB::table('tags')->where('tag_name', $name)->exists()) {
                continue;
            }

            DB::table('tags')->insert([
                'tag_name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $created++;
        }

        $this->command->info("{$created} tag baru ditambahkan.");
    }
}
