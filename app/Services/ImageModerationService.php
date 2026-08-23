<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImageModerationService
{
    public function moderateImage(string $imagePath): array
    {
        if (! config('services.content_moderation.enabled')) {
            return $this->safeResult('AI moderation is disabled.');
        }

        if (! file_exists($imagePath)) {
            return $this->failedResult('Image file was not found for AI moderation.');
        }

        $apiKey = config('services.openai.api_key');

        if (blank($apiKey)) {
            return $this->failedResult('OpenAI API key is not configured.');
        }

        try {
            $base64Image = base64_encode(file_get_contents($imagePath));
            $mimeType = mime_content_type($imagePath) ?: 'image/jpeg';

            $response = Http::timeout(30)
                ->withToken($apiKey)
                ->post('https://api.openai.com/v1/moderations', [
                    'model' => config('services.openai.moderation_model', 'omni-moderation-latest'),
                    'input' => [
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:{$mimeType};base64,{$base64Image}",
                            ],
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                Log::warning('OpenAI moderation request failed.', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return $this->failedResult('AI moderation request failed.');
            }

            return $this->parseResponse($response->json());
        } catch (\Throwable $exception) {
            Log::warning('OpenAI moderation exception.', [
                'message' => $exception->getMessage(),
            ]);

            return $this->failedResult('AI moderation could not be completed.');
        }
    }

    private function parseResponse(array $response): array
    {
        $result = $response['results'][0] ?? null;

        if (! $result) {
            return $this->failedResult('AI moderation returned an invalid response.');
        }

        $categories = $result['categories'] ?? [];
        $categoryScores = $result['category_scores'] ?? [];

        $flaggedCategory = $this->getFlaggedCategory($categories, $categoryScores);
        $highestScoreCategory = $this->getHighestScoreCategory($categoryScores);

        $threshold = (float) config('services.content_moderation.threshold', 0.70);

        $moderationCategory = $flaggedCategory
            ?? $highestScoreCategory['category']
            ?? 'safe';

        $moderationScore = $flaggedCategory
            ? (float) ($categoryScores[$flaggedCategory] ?? 1)
            : (float) ($highestScoreCategory['score'] ?? 0);

        $isFlagged = (bool) ($result['flagged'] ?? false)
            || $moderationScore >= $threshold;

        return [
            'is_flagged' => $isFlagged,
            'moderation_category' => $isFlagged
                ? $this->normalizeCategory($moderationCategory)
                : 'safe',
            'moderation_score' => round($moderationScore, 4),
            'reason' => $isFlagged
                ? 'Potentially unsafe content detected by AI moderation.'
                : 'No unsafe content detected by AI moderation.',
            'raw_categories' => $categories,
            'raw_category_scores' => $categoryScores,
        ];
    }

    private function getFlaggedCategory(array $categories, array $categoryScores): ?string
    {
        $flaggedCategories = collect($categories)
            ->filter(fn($isFlagged): bool => (bool) $isFlagged)
            ->keys();

        if ($flaggedCategories->isEmpty()) {
            return null;
        }

        return $flaggedCategories
            ->sortByDesc(fn(string $category): float => (float) ($categoryScores[$category] ?? 0))
            ->first();
    }

    private function getHighestScoreCategory(array $categoryScores): ?array
    {
        if (empty($categoryScores)) {
            return null;
        }

        $category = collect($categoryScores)
            ->sortDesc()
            ->keys()
            ->first();

        return [
            'category' => $category,
            'score' => (float) ($categoryScores[$category] ?? 0),
        ];
    }

    private function normalizeCategory(string $category): string
    {
        return match (true) {
            str_contains($category, 'sexual') => 'adult',
            str_contains($category, 'violence') => 'violence',
            str_contains($category, 'harassment') => 'harassment',
            str_contains($category, 'hate') => 'hate',
            str_contains($category, 'self-harm') => 'self_harm',
            str_contains($category, 'illicit') => 'illegal',
            default => $category ?: 'unknown',
        };
    }

    private function safeResult(string $reason): array
    {
        return [
            'is_flagged' => false,
            'moderation_category' => 'safe',
            'moderation_score' => 0,
            'reason' => $reason,
            'raw_categories' => [],
            'raw_category_scores' => [],
        ];
    }

    private function failedResult(string $reason): array
    {
        return [
            'is_flagged' => true,
            'moderation_category' => 'unknown',
            'moderation_score' => null,
            'reason' => $reason,
            'raw_categories' => [],
            'raw_category_scores' => [],
        ];
    }
}
