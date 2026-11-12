<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PrivateStorage
{
    public static function name(): string
    {
        return config('filesystems.private_disk', 'local');
    }

    public static function disk(): Filesystem
    {
        return Storage::disk(self::name());
    }

    public static function isLocal(): bool
    {
        return config('filesystems.disks.' . self::name() . '.driver') === 'local';
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
