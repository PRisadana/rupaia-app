<?php

namespace App\Services\Distribution;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;
use ZipArchive;

class BundleZipService
{
    public function generate(Transaction $transaction, TransactionItem $item): string
    {
        if ($item->item_type !== 'bundle' || ! $item->folder) {
            throw new RuntimeException(
                'Invalid bundle transaction item.'
            );
        }

        $contents = $item->folder->getBundleContents();

        if ($contents->isEmpty()) {
            throw new RuntimeException(
                'Bundle does not contain purchasable content.'
            );
        }

        $directory = "transaction_files/" . "{$transaction->id}/bundles";
        $zipPath = "{$directory}/" . "bundle_item_{$item->id}.zip";
        $disk = Storage::disk('local');
        $disk->makeDirectory($directory);
        $absoluteZipPath = $disk->path($zipPath);
        $zip = new ZipArchive();

        $opened = $zip->open(
            $absoluteZipPath,
            ZipArchive::CREATE | ZipArchive::OVERWRITE
        );

        if ($opened !== true) {
            throw new RuntimeException(
                'Unable to create bundle ZIP.'
            );
        }

        try {
            foreach ($contents as $content) {
                if (
                    ! $content->path_hi_res || ! Storage::disk('local')
                        ->exists($content->path_hi_res)
                ) {
                    throw new RuntimeException(
                        "Hi-res file for content "
                            . "{$content->id} was not found."
                    );
                }

                $sourcePath = Storage::disk('local')->path(
                    $content->path_hi_res
                );

                $fileName = $content->id . '_' . basename($content->path_hi_res);

                if (! $zip->addFile($sourcePath, $fileName)) {
                    throw new RuntimeException(
                        "Unable to add content "
                            . "{$content->id} to ZIP."
                    );
                }
            }

            if (! $zip->close()) {
                throw new RuntimeException(
                    'Unable to finalize bundle ZIP.'
                );
            }
        } catch (Throwable $e) {
            try {
                $zip->close();
            } catch (Throwable) {
                //
            }

            $disk->delete(
                $zipPath
            );

            throw $e;
        }

        return $zipPath;
    }
}
