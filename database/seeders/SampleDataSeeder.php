<?php

namespace Database\Seeders;

use App\Services\Content\ImageHashService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Seeder data contoh (demo dan uji beban).
 *
 * Tidak dipanggil dari DatabaseSeeder. Jalankan secara eksplisit:
 * php artisan db:seed --class=SampleDataSeeder
 */
class SampleDataSeeder extends Seeder
{
    private const SELLER_COUNT = 5;

    private const PRICES = [25000, 35000, 50000, 75000, 100000, 150000];

    private const LICENSE_CODES = [
        'personal',
        'commercial',
        'editorial',
        'extended_commercial',
        'educational',
        'digital_use',
        'print_use',
    ];

    private const FALLBACK_TAGS = [
        'nature',
        'landscape',
        'travel',
        'lifestyle',
        'street',
        'architecture',
    ];

    private const SELLER_NAMES = [
        'Ayu Pratama',
        'Bagus Wirawan',
        'Citra Anindya',
        'Dimas Saputra',
        'Eka Kusuma',
    ];

    private const BUNDLE_NAMES = [
        'Nature Bundle',
        'Urban Bundle',
        'Travel Pack',
        'Lifestyle Pack',
        'Mood Collection',
    ];

    public function run(): void
    {
        $photos = $this->listPhotos(database_path('seeders/assets/photos'));

        if (empty($photos)) {
            $this->command->warn('Tidak ada foto di database/seeders/assets/photos, seeder dihentikan.');

            return;
        }

        $licenseIds = DB::table('licenses')
            ->where('is_active', true)
            ->whereIn('code', self::LICENSE_CODES)
            ->pluck('id', 'code')
            ->all();

        $tagIds = DB::table('tags')->pluck('id', 'tag_name')->all();

        if (empty($licenseIds) || empty($tagIds)) {
            $this->command->warn('Data lisensi atau tag kosong. Jalankan DatabaseSeeder terlebih dahulu.');

            return;
        }

        $licenseCodes = array_values(array_intersect(self::LICENSE_CODES, array_keys($licenseIds)));
        $hashService = app(ImageHashService::class);

        $sellerIds = $this->createSellers();
        $folders = $this->createFolders($sellerIds, $licenseIds['commercial'] ?? reset($licenseIds));

        $created = 0;

        foreach ($photos as $index => $source) {
            $sellerIndex = $index % self::SELLER_COUNT;
            $sellerId = $sellerIds[$sellerIndex];
            $position = intdiv($index, self::SELLER_COUNT);

            $inBundle = $position % 4 === 3;
            $isSingleSale = ! $inBundle && $index % 7 === 6;

            $folder = $inBundle
                ? $folders[$sellerIndex]['bundle']
                : $folders[$sellerIndex]['portfolio'];

            $stem = pathinfo($source, PATHINFO_FILENAME);
            $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION));
            $fileName = 'seed_' . Str::slug($stem) . '.' . $extension;

            $pathHiRes = "content_file/{$sellerId}/hi_res/{$fileName}";
            $pathLowRes = "content_file/{$sellerId}/low_res/{$fileName}";

            if (DB::table('contents')->where('path_hi_res', $pathHiRes)->exists()) {
                continue;
            }

            Storage::disk('local')->put($pathHiRes, file_get_contents($source));
            Storage::disk('public')->put($pathLowRes, $this->makeLowRes($source));

            $title = $this->makeTitle($stem);
            $createdAt = now()->subDays($index % 45)->subMinutes($index * 7);

            $contentId = DB::table('contents')->insertGetId([
                'seller_id' => $sellerId,
                'folder_id' => $folder['id'],
                'license_id' => $inBundle
                    ? $folder['license_id']
                    : $licenseIds[$licenseCodes[$index % count($licenseCodes)]],
                'content_title' => $title,
                'content_description' => "Sample photograph \"{$title}\" provided for demonstration purposes.",
                'price' => $isSingleSale
                    ? self::PRICES[count(self::PRICES) - 1] * 2
                    : self::PRICES[$index % count(self::PRICES)],
                'sale_type' => $isSingleSale ? 'single_sale' : 'multi_sale',
                'sale_status' => 'available',
                'path_hi_res' => $pathHiRes,
                'path_low_res' => $pathLowRes,
                'visibility' => 'public',
                'status' => 'active',
                'perceptual_hash' => $hashService->generateHash($source),
                'similar_content_id' => null,
                'similarity_distance' => null,
                'moderation_score' => 0,
                'moderation_category' => null,
                'validation_reason' => 'Sample data: validation skipped by seeder.',
                'validated_at' => $createdAt,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $tagRows = array_map(fn(int $tagId) => [
                'content_id' => $contentId,
                'tag_id' => $tagId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ], $this->resolveTagIds($stem, $index, $tagIds));

            DB::table('content_tags')->insertOrIgnore($tagRows);

            $created++;
        }

        $this->updateBundlePrices($folders);

        $this->command->info("{$created} konten contoh ditambahkan dari " . count($photos) . ' foto.');
    }

    /**
     * Mengambil daftar foto (jpg, jpeg, png) terurut agar hasil deterministik.
     *
     * @return array<int, string>
     */
    private function listPhotos(string $directory): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $files = array_filter(
            glob($directory . '/*') ?: [],
            fn(string $path) => is_file($path)
                && in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'], true)
        );

        sort($files);

        return array_values($files);
    }

    /**
     * Membuat akun seller contoh. Akun yang sudah ada dilewati.
     *
     * @return array<int, int>
     */
    private function createSellers(): array
    {
        $password = config('rupaia.seed.sample_password') ?: Str::random(12);
        $ids = [];
        $createdAny = false;

        for ($i = 0; $i < self::SELLER_COUNT; $i++) {
            $email = 'seller' . ($i + 1) . '.demo@rupaia.test';

            $existingId = DB::table('users')->where('email', $email)->value('id');

            if ($existingId) {
                $ids[] = $existingId;

                continue;
            }

            $ids[] = DB::table('users')->insertGetId([
                'name' => self::SELLER_NAMES[$i],
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'seller',
                'bio' => 'Demo seller account created by the sample data seeder.',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $createdAny = true;
        }

        if ($createdAny && ! config('rupaia.seed.sample_password')) {
            $this->command->warn("Password akun seller contoh (catat sekarang): {$password}");
        }

        return $ids;
    }

    /**
     * Membuat satu folder biasa dan satu folder bundle untuk setiap seller.
     *
     * @param  array<int, int>  $sellerIds
     * @return array<int, array{portfolio: array, bundle: array}>
     */
    private function createFolders(array $sellerIds, int $bundleLicenseId): array
    {
        $folders = [];

        foreach ($sellerIds as $index => $sellerId) {
            $folders[$index] = [
                'portfolio' => $this->firstOrCreateFolder($sellerId, 'Portfolio', [
                    'folder_description' => 'Selected works from the portfolio.',
                    'is_bundle' => false,
                    'license_id' => null,
                    'bundle_price' => null,
                    'allow_individual_sale' => true,
                ]),
                'bundle' => $this->firstOrCreateFolder($sellerId, self::BUNDLE_NAMES[$index], [
                    'folder_description' => 'Curated photo bundle sold as one package.',
                    'is_bundle' => true,
                    'license_id' => $bundleLicenseId,
                    'bundle_price' => 100000,
                    'allow_individual_sale' => true,
                ]),
            ];
        }

        return $folders;
    }

    /**
     * @return array{id: int, license_id: int|null}
     */
    private function firstOrCreateFolder(int $sellerId, string $name, array $attributes): array
    {
        $existing = DB::table('folders')
            ->where('seller_id', $sellerId)
            ->whereNull('parent_id')
            ->where('folder_name', $name)
            ->first();

        if ($existing) {
            return ['id' => $existing->id, 'license_id' => $existing->license_id];
        }

        $id = DB::table('folders')->insertGetId($attributes + [
            'seller_id' => $sellerId,
            'parent_id' => null,
            'folder_name' => $name,
            'visibility' => 'public',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ['id' => $id, 'license_id' => $attributes['license_id']];
    }

    /**
     * Harga bundle ditetapkan 80% dari total harga isinya, dibulatkan ke ribuan.
     */
    private function updateBundlePrices(array $folders): void
    {
        foreach ($folders as $folder) {
            $total = (float) DB::table('contents')
                ->where('folder_id', $folder['bundle']['id'])
                ->sum('price');

            if ($total <= 0) {
                continue;
            }

            DB::table('folders')
                ->where('id', $folder['bundle']['id'])
                ->update(['bundle_price' => round($total * 0.8 / 1000) * 1000]);
        }
    }

    /**
     * Judul diambil dari nama file tanpa angka urut di belakangnya.
     */
    private function makeTitle(string $stem): string
    {
        $clean = preg_replace('/[\s\-_]*\d+$/', '', $stem);
        $title = Str::headline($clean ?: $stem);

        return $title !== '' ? $title : 'Untitled Photo';
    }

    /**
     * Tag dicocokkan dari kata (dan pasangan kata) pada nama file.
     * Jika kurang dari tiga, ditambah tag umum secara bergilir.
     *
     * @param  array<string, int>  $tagIds
     * @return array<int, int>
     */
    private function resolveTagIds(string $stem, int $index, array $tagIds): array
    {
        $words = array_values(array_filter(preg_split('/[\s\-_]+/', strtolower($stem)) ?: []));

        $candidates = $words;

        for ($i = 0; $i < count($words) - 1; $i++) {
            $candidates[] = $words[$i] . ' ' . $words[$i + 1];
        }

        $matched = [];

        foreach ($candidates as $candidate) {
            if (isset($tagIds[$candidate])) {
                $matched[$candidate] = $tagIds[$candidate];
            }
        }

        $fallbackCount = count(self::FALLBACK_TAGS);

        for ($i = 0; count($matched) < 3 && $i < $fallbackCount; $i++) {
            $name = self::FALLBACK_TAGS[($index + $i) % $fallbackCount];

            if (isset($tagIds[$name])) {
                $matched[$name] = $tagIds[$name];
            }
        }

        return array_values(array_slice($matched, 0, 5));
    }

    private function makeLowRes(string $source): string
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($source);

        $image->scale(width: 720);

        $watermarkPath = public_path('aset/watermark.png');

        if (is_file($watermarkPath)) {
            $watermark = $manager->read($watermarkPath);
            $watermark->scaleDown(width: (int) ($image->width() * 0.4));
            $image->place($watermark, 'center', 0, 0, 50);
        }

        return (string) $image->encode();
    }
}
