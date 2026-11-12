<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MigratePrivateFilesToS3 extends Command
{
    protected $signature = 'rupaia:pindah-berkas-privat {--dry-run : Hanya menghitung, tanpa menyalin}';

    protected $description = 'Menyalin berkas privat (hi-res, hasil final, ZIP bundel) dari disk lokal ke S3';

    private const PREFIXES = ['content_file', 'transactions', 'transaction_files'];

    public function handle(): int
    {
        $source = Storage::disk('local');
        $target = Storage::disk('s3');
        $dryRun = (bool) $this->option('dry-run');

        $copied = 0;
        $skipped = 0;
        $failed = 0;
        $bytes = 0;

        foreach (self::PREFIXES as $prefix) {
            foreach ($source->allFiles($prefix) as $path) {
                $size = $source->size($path);

                try {
                    if ($target->exists($path) && $target->size($path) === $size) {
                        $skipped++;

                        continue;
                    }

                    if (! $dryRun) {
                        $stream = $source->readStream($path);
                        $target->put($path, $stream);

                        if (is_resource($stream)) {
                            fclose($stream);
                        }
                    }

                    $copied++;
                    $bytes += $size;
                    $this->line(($dryRun ? '[uji]   ' : '[salin] ') . $path);
                } catch (Throwable $e) {
                    $failed++;
                    $this->error("[gagal] {$path}: {$e->getMessage()}");
                }
            }
        }

        $this->info(sprintf(
            'Selesai: %d disalin (%.1f MB), %d dilewati, %d gagal.',
            $copied,
            $bytes / 1048576,
            $skipped,
            $failed
        ));

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
