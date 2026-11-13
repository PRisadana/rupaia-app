<?php

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\Content;
use App\Models\User;
use App\Services\Checkout\PurchaseValidationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LoadTestSeeder extends Seeder
{
    private const JUMLAH_PEMBELI = 200;
    private const TRANSAKSI_PER_PEMBELI = 12;
    private const DOMAIN_EMAIL = 'loadtest.test';

    public function run(): void
    {
        if (DB::table('users')->where('email', 'like', '%@' . self::DOMAIN_EMAIL)->exists()) {
            $this->command->error('Data uji sudah ada. Jalankan LoadTestCleanupSeeder lebih dulu.');

            return;
        }

        $layanan = app(PurchaseValidationService::class);
        $pembeliContoh = new User();
        $pembeliContoh->id = 0;
        $pembeliContoh->role = 'buyer';

        $contents = Content::query()
            ->where('visibility', 'public')
            ->where('status', 'active')
            ->where('sale_status', 'available')
            ->where('sale_type', '!=', 'single_sale')
            ->with(['license', 'folder'])
            ->orderBy('id')
            ->get()
            ->filter(fn(Content $c) => $c->license !== null
                && (float) $c->price > 0
                && (float) $c->price === floor((float) $c->price)
                && $layanan->contentError($c, $pembeliContoh) === null)
            ->values();

        $this->command->info("Konten yang dapat dibeli satuan: {$contents->count()}");

        if ($contents->count() < 10) {
            $this->command->error('Konten yang dapat dibeli satuan kurang dari 10. Jalankan SampleDataSeeder dulu.');

            return;
        }

        $now = now();
        $barisCsv = [];
        $pembeliPertama = [];

        DB::transaction(function () use ($contents, $now, &$barisCsv, &$pembeliPertama) {
            $jumlah = $contents->count();
            $offset = intdiv($jumlah, 2);

            for ($i = 1; $i <= self::JUMLAH_PEMBELI; $i++) {
                $email = sprintf('lt-%03d@%s', $i, self::DOMAIN_EMAIL);
                $password = Str::random(16);

                $userId = DB::table('users')->insertGetId([
                    'name' => sprintf('LT Buyer %03d', $i),
                    'email' => $email,
                    'email_verified_at' => $now,
                    'password' => Hash::make($password),
                    'role' => 'buyer',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $kontenTerpakai = collect();
                $idTransaksiSelesai = null;

                for ($t = 1; $t <= self::TRANSAKSI_PER_PEMBELI; $t++) {
                    [$status, $pembayaran] = match (true) {
                        $t <= 10 => ['completed', 'success'],
                        $t === 11 => ['pending', 'pending'],
                        default => ['cancelled', 'expired'],
                    };

                    $items = [];
                    $jumlahItem = $t % 2 === 0 ? 2 : 1;
                    for ($j = 0; $j < $jumlahItem; $j++) {
                        $items[] = $contents[($i * 5 + $t * 2 + $offset + $j) % $jumlah];
                    }

                    $total = collect($items)->sum(fn($c) => (float) $c->price);
                    $dibuat = $now->copy()->subDays($t * 3)->subMinutes($i)->toDateTimeString();
                    $selesai = $status === 'completed';

                    $transaksiId = DB::table('transactions')->insertGetId([
                        'buyer_id' => $userId,
                        'order_code' => sprintf('RPA-LT%03d-%02d', $i, $t),
                        'status' => $status,
                        'total_amount' => $total,
                        'snap_token' => null,
                        'payment_status' => $pembayaran,
                        'paid_at' => $pembayaran === 'success' ? $dibuat : null,
                        'invoice_number' => $pembayaran === 'success' ? sprintf('INV-LT%03d-%02d', $i, $t) : null,
                        'created_at' => $dibuat,
                        'updated_at' => $dibuat,
                    ]);

                    foreach ($items as $content) {
                        DB::table('transaction_items')->insert([
                            'transaction_id' => $transaksiId,
                            'seller_id' => $content->seller_id,
                            'content_id' => $content->id,
                            'folder_id' => null,
                            'preset_id' => null,
                            'payout_id' => null,
                            'license_id' => $content->license->id,
                            'item_type' => 'content',
                            'item_name_snapshot' => $content->content_title,
                            'license_name_snapshot' => $content->license->name,
                            'license_terms_snapshot' => $content->license->terms ?? '',
                            'price_snapshot' => $content->price,
                            'final_file_path' => $selesai ? sprintf('transactions/%d/uji/final.jpg', $transaksiId) : null,
                            'commission_amount' => null,
                            'seller_amount' => null,
                            'download_available_at' => $selesai ? $dibuat : null,
                            'download_expires_at' => $selesai ? $now->copy()->addDays(30)->toDateTimeString() : null,
                            'download_count' => 0,
                            'created_at' => $dibuat,
                            'updated_at' => $dibuat,
                        ]);

                        $kontenTerpakai->push($content->id);
                    }

                    if ($t === 1) {
                        $idTransaksiSelesai = $transaksiId;
                    }
                }

                $kolam = $contents->reject(fn($c) => $kontenTerpakai->contains($c->id))->values();
                if ($kolam->count() < 2) {
                    $kolam = $contents;
                }

                $cartId = DB::table('carts')->insertGetId([
                    'buyer_id' => $userId,
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                for ($k = 0; $k < 2; $k++) {
                    $content = $kolam[($i + $k) % $kolam->count()];

                    DB::table('cart_items')->insert([
                        'cart_id' => $cartId,
                        'content_id' => $content->id,
                        'folder_id' => null,
                        'preset_id' => null,
                        'item_type' => 'content',
                        'price_snapshot' => $content->price,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $barisCsv[] = [$email, $password, $idTransaksiSelesai];

                if ($i === 1) {
                    $pembeliPertama = ['id' => $userId, 'cart_id' => $cartId];
                }
            }
        });

        $this->tulisCsv($barisCsv);
        $this->periksaSampel($pembeliPertama);

        $this->command->info(sprintf(
            'Selesai: %d pembeli, %d transaksi per pembeli. Berkas CSV ada di storage/app/loadtest.',
            self::JUMLAH_PEMBELI,
            self::TRANSAKSI_PER_PEMBELI
        ));
    }

    private function tulisCsv(array $barisPembeli): void
    {
        $folder = storage_path('app/loadtest');
        if (! is_dir($folder)) {
            mkdir($folder, 0700, true);
        }

        $berkasPembeli = $folder . '/pembeli.csv';
        $isi = "email,password,transaction_id\n";
        foreach ($barisPembeli as $baris) {
            $isi .= implode(',', $baris) . "\n";
        }
        file_put_contents($berkasPembeli, $isi);
        chmod($berkasPembeli, 0600);

        $idKonten = Content::query()
            ->where('visibility', 'public')
            ->where('status', 'active')
            ->where('sale_status', 'available')
            ->orderBy('id')
            ->pluck('id');

        $berkasKonten = $folder . '/konten.csv';
        file_put_contents($berkasKonten, "content_id\n" . $idKonten->implode("\n") . "\n");
        chmod($berkasKonten, 0600);
    }

    private function periksaSampel(array $pembeliPertama): void
    {
        if ($pembeliPertama === []) {
            return;
        }

        $layanan = app(PurchaseValidationService::class);
        $pembeli = User::find($pembeliPertama['id']);
        $itemKeranjang = CartItem::with(['content.folder', 'content.license'])
            ->where('cart_id', $pembeliPertama['cart_id'])
            ->get();

        $bermasalah = false;
        foreach ($itemKeranjang as $item) {
            $galat = $layanan->cartItemError($item, $pembeli);
            $harga = $layanan->currentPrice($item);

            if ($galat || $harga === null || $harga !== (float) $item->price_snapshot) {
                $bermasalah = true;
                $this->command->warn("Item keranjang {$item->id} tidak lolos validasi: " . ($galat ?: 'harga tidak sama'));
            }
        }

        if (! $bermasalah) {
            $this->command->info('Pemeriksaan sampel lolos: keranjang akun pertama valid untuk checkout.');
        }
    }
}
