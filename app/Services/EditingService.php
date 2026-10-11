<?php

namespace App\Services;

use App\Models\Content;
use App\Models\Preset;
use App\Models\TransactionItem;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class EditingService
{
    public function generatePreview(Content $content, Preset $preset): string
    {
        if (! $preset->is_active) {
            throw new RuntimeException(
                'Preset is no longer available.'
            );
        }

        if (! Storage::disk('public')->exists(
            $content->path_low_res
        )) {
            throw new RuntimeException(
                'Preview image not found.'
            );
        }

        $imagePath = Storage::disk('public')->path($content->path_low_res);

        $lutFilename = $this->prepareLut($preset);

        $response = $this->sendToEditingService(
            $imagePath,
            $lutFilename,
            'apply-lut-preview',
            30
        );

        return $response->body();
    }

    public function generateFinal(Content $content, Preset $preset, TransactionItem $item): string
    {
        if (! Storage::disk('local')->exists(
            $content->path_hi_res
        )) {
            throw new RuntimeException(
                'Hi-res source image not found.'
            );
        }

        $imagePath = Storage::disk('local')->path($content->path_hi_res);

        $lutFilename = $this->prepareLut($preset);

        $response = $this->sendToEditingService(
            $imagePath,
            $lutFilename,
            'apply-lut-final',
            120
        );

        $finalPath = sprintf(
            'transactions/%d/items/%d/final-preset-%d.jpg',
            $item->transaction_id,
            $item->id,
            $preset->id
        );

        Storage::disk('local')->put(
            $finalPath,
            $response->body()
        );

        return $finalPath;
    }


    private function prepareLut(Preset $preset): string
    {
        if (! Storage::disk('public')->exists(
            $preset->preset_file_path
        )) {
            throw new RuntimeException(
                'Preset LUT file not found.'
            );
        }

        $sourcePath = Storage::disk('public')
            ->path($preset->preset_file_path);

        $pythonLutDir = base_path(
            'editing-service/luts'
        );

        if (! is_dir($pythonLutDir)) {
            mkdir(
                $pythonLutDir,
                0755,
                true
            );
        }

        $lutFilename = basename($sourcePath);

        $targetPath =
            $pythonLutDir
            . DIRECTORY_SEPARATOR
            . $lutFilename;

        if (
            ! file_exists($targetPath)
            ||
            filemtime($targetPath)
            < filemtime($sourcePath)
        ) {
            copy(
                $sourcePath,
                $targetPath
            );
        }

        return $lutFilename;
    }

    private function sendToEditingService(
        string $imagePath,
        string $lutFilename,
        string $endpoint,
        int $timeout
    ): Response {
        $baseUrl = rtrim(
            config(
                'services.editing.url',
                'http://127.0.0.1:5001'
            ),
            '/'
        );

        $response = Http::timeout($timeout)
            ->attach(
                'image',
                file_get_contents($imagePath),
                basename($imagePath)
            )
            ->post(
                "{$baseUrl}/{$endpoint}",
                [
                    'lut_filename'
                    => $lutFilename,
                ]
            );

        if (! $response->successful()) {
            throw new RuntimeException(
                'Editing service failed: '
                    . $response->body()
            );
        }
        return $response;
    }
}
