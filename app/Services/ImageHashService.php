<?php

namespace App\Services;

use App\Models\Content;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageHashService
{
    private const HASH_SIZE = 8;

    public function __construct(
        private readonly int $threshold = 10
    ) {}

    public function generateHash(string $imagePath): string
    {
        $manager = new ImageManager(new Driver());

        $image = $manager->read($imagePath)
            ->resize(self::HASH_SIZE, self::HASH_SIZE)
            ->greyscale();

        $pixels = [];
        $totalBrightness = 0;

        for ($y = 0; $y < self::HASH_SIZE; $y++) {
            for ($x = 0; $x < self::HASH_SIZE; $x++) {
                $color = $image->pickColor($x, $y);

                $brightness = $this->extractBrightness($color);

                $pixels[] = $brightness;
                $totalBrightness += $brightness;
            }
        }

        $averageBrightness = $totalBrightness / count($pixels);

        return collect($pixels)
            ->map(fn(int $brightness): string => $brightness >= $averageBrightness ? '1' : '0')
            ->implode('');
    }

    public function hammingDistance(string $hashA, string $hashB): int
    {
        if (strlen($hashA) !== strlen($hashB)) {
            return PHP_INT_MAX;
        }

        $distance = 0;

        for ($i = 0; $i < strlen($hashA); $i++) {
            if ($hashA[$i] !== $hashB[$i]) {
                $distance++;
            }
        }

        return $distance;
    }

    public function findSimilarContent(string $hash, ?int $excludedContentId = null): array
    {
        $query = Content::query()
            ->whereNotNull('perceptual_hash')
            ->where('status', 'active');

        if ($excludedContentId !== null) {
            $query->where('id', '!=', $excludedContentId);
        }

        $contents = $query->get([
            'id',
            'content_title',
            'perceptual_hash',
            'path_low_res',
            'folder_id',
            'seller_id',
            'license_id',
            'status',
        ]);

        $closestContent = null;
        $closestDistance = null;

        foreach ($contents as $content) {
            $distance = $this->hammingDistance($hash, $content->perceptual_hash);

            if ($closestDistance === null || $distance < $closestDistance) {
                $closestDistance = $distance;
                $closestContent = $content;
            }
        }

        return [
            'is_similar' => $closestDistance !== null && $closestDistance <= $this->threshold,
            'similar_content' => $closestContent,
            'distance' => $closestDistance,
            'threshold' => $this->threshold,
        ];
    }

    public function validateImage(string $imagePath, ?int $excludedContentId = null): array
    {
        $hash = $this->generateHash($imagePath);

        $similarityResult = $this->findSimilarContent($hash, $excludedContentId);

        return [
            'perceptual_hash' => $hash,
            'is_similar' => $similarityResult['is_similar'],
            'similar_content' => $similarityResult['similar_content'],
            'similarity_distance' => $similarityResult['distance'],
            'threshold' => $similarityResult['threshold'],
            'validation_reason' => $similarityResult['is_similar']
                ? 'Similar content detected by perceptual hash check.'
                : 'No similar content detected by perceptual hash check.',
        ];
    }

    private function extractBrightness(mixed $color): int
    {
        if (is_array($color)) {
            return (int) round(($color[0] + $color[1] + $color[2]) / 3);
        }

        if (
            is_object($color) &&
            method_exists($color, 'red') &&
            method_exists($color, 'green') &&
            method_exists($color, 'blue')
        ) {
            $red = $color->red();
            $green = $color->green();
            $blue = $color->blue();

            if (
                is_object($red) &&
                is_object($green) &&
                is_object($blue) &&
                method_exists($red, 'toInt') &&
                method_exists($green, 'toInt') &&
                method_exists($blue, 'toInt')
            ) {
                return (int) round((
                    $red->toInt() +
                    $green->toInt() +
                    $blue->toInt()
                ) / 3);
            }
        }

        return 0;
    }
}
