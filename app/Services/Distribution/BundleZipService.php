<?php

namespace App\Services\Distribution;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Support\PrivateStorage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use ZipArchive;

class BundleZipService
{
    public function generate(Transaction $transaction, TransactionItem $item): string
    {
        if ($item->item_type !== 'bundle' || ! $item->folder) {
            throw new RuntimeException('Invalid bundle transaction item.');
        }

        $contents = $item->folder->getBundleContents();

        if ($contents->isEmpty()) {
            throw new RuntimeException('Bundle does not contain purchasable content.');
        }

        $disk = PrivateStorage::disk();
        $zipPath = "transaction_files/{$transaction->id}/bundles/bundle_item_{$item->id}.zip";

        $tempDir = storage_path('app/tmp/' . Str::uuid());
        File::ensureDirectoryExists($tempDir);
        $tempZip = $tempDir . DIRECTORY_SEPARATOR . 'bundle.zip';

        $zip = new ZipArchive();
        $opened = $zip->open($tempZip, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($opened !== true) {
            File::deleteDirectory($tempDir);

            throw new RuntimeException('Unable to create bundle ZIP.');
        }

        try {
            foreach ($contents as $content) {
                if (! $content->path_hi_res || ! $disk->exists($content->path_hi_res)) {
                    throw new RuntimeException(
                        "Hi-res file for content {$content->id} was not found."
                    );
                }

                $sourcePath = PrivateStorage::localPath($content->path_hi_res, $tempDir);
                $fileName = $content->id . '_' . basename($content->path_hi_res);

                if (! $zip->addFile($sourcePath, $fileName)) {
                    throw new RuntimeException(
                        "Unable to add content {$content->id} to ZIP."
                    );
                }
            }

            if (! $zip->close()) {
                throw new RuntimeException('Unable to finalize bundle ZIP.');
            }

            $stream = fopen($tempZip, 'rb');

            try {
                if ($disk->put($zipPath, $stream) === false) {
                    throw new RuntimeException('Unable to store bundle ZIP.');
                }
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        } catch (Throwable $e) {
            try {
                $zip->close();
            } catch (Throwable) {
                //
            }

            $disk->delete($zipPath);

            throw $e;
        } finally {
            File::deleteDirectory($tempDir);
        }

        return $zipPath;
    }
}
