<?php

namespace App\Support;

use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PrivateStorage
{
    public static function name(): string
    {
        return config('filesystems.private_disk', 'local');
    }

    public static function disk(): FilesystemAdapter
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(self::name());

        return $disk;
    }

    public static function isLocal(): bool
    {
        return config('filesystems.disks.' . self::name() . '.driver') === 'local';
    }

    public static function copy(string $from, string $to): void
    {
        $disk = self::disk();

        if (self::isLocal()) {
            if (! $disk->copy($from, $to)) {
                throw new RuntimeException("Gagal menyalin {$from} ke {$to}.");
            }

            return;
        }

        if (! $disk instanceof AwsS3V3Adapter) {
            throw new RuntimeException('Disk privat bukan disk lokal maupun disk S3.');
        }

        $bucket = config('filesystems.disks.' . self::name() . '.bucket');
        $encodedKey = implode('/', array_map('rawurlencode', explode('/', ltrim($from, '/'))));

        $disk->getClient()->copyObject([
            'Bucket' => $bucket,
            'Key' => ltrim($to, '/'),
            'CopySource' => $bucket . '/' . $encodedKey,
        ]);
    }

    public static function localPath(string $path, string $tempDir): string
    {
        if (self::isLocal()) {
            return self::disk()->path($path);
        }

        $source = self::disk()->readStream($path);

        if (! is_resource($source)) {
            throw new RuntimeException("Berkas {$path} tidak dapat dibaca dari penyimpanan.");
        }

        $target = $tempDir . DIRECTORY_SEPARATOR . md5($path) . '_' . basename($path);
        $output = fopen($target, 'wb');

        stream_copy_to_stream($source, $output);

        fclose($output);
        fclose($source);

        return $target;
    }
}
