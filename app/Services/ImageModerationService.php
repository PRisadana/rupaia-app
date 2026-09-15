<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImageModerationService
{
    public function moderateImage(
        string $imagePath,
        ?string $contentTitle = null,
        ?string $contentDescription = null
    ): array {

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

            $textInput = trim(implode("\n", array_filter([
                $contentTitle ? 'Content title: ' . $contentTitle : null,
                $contentDescription ? 'Content description: ' . $contentDescription : null,
            ])));

            $moderationInput = [];

            if ($textInput !== '') {
                $moderationInput[] = [
                    'type' => 'text',
                    'text' => $textInput,
                ];
            }

            $moderationInput[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => "data:{$mimeType};base64,{$base64Image}",
                ],
            ];

            Log::info('OpenAI moderation input summary.', [
                'has_title' => filled($contentTitle),
                'has_description' => filled($contentDescription),
                'text_length' => strlen($textInput),
                'has_image' => true,
            ]);

            $response = Http::timeout(30)
                ->withToken($apiKey)
                ->post('https://api.openai.com/v1/moderations', [
                    'model' => config('services.openai.moderation_model', 'omni-moderation-latest'),
                    'input' => $moderationInput,
                ]);

            if (! $response->successful()) {
                Log::warning('OpenAI moderation request failed.', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return $this->failedResult('AI moderation request failed.');
            }

            $rawResponse = $response->json();

            Log::info('OpenAI moderation request succeeded.', [
                'status' => $response->status(),
                'request_id' => $response->header('x-request-id'),
            ]);

            if (config('services.content_moderation.log_raw_response')) {
                Log::info('OpenAI moderation raw JSON response.', [
                    'raw_response' => $rawResponse,
                ]);
            }

            $parsedResult = $this->parseResponse($rawResponse);

            Log::info('OpenAI moderation parsed result used by system.', [
                'parsed_result' => $parsedResult,
            ]);

            return $parsedResult;
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

        $flagged = (bool) ($result['flagged'] ?? false);
        $categories = $result['categories'] ?? [];
        $categoryScores = $result['category_scores'] ?? [];
        $categoryAppliedInputTypes = $result['category_applied_input_types'] ?? [];

        Log::info('OpenAI moderation fields extracted from raw JSON.', [
            'flagged' => $flagged,
            'categories' => $categories,
            'category_scores' => $categoryScores,
            'category_applied_input_types' => $categoryAppliedInputTypes,
        ]);

        $flaggedCategory = $this->getFlaggedCategory($categories, $categoryScores);
        $highestScoreCategory = $this->getHighestScoreCategory($categoryScores);

        $threshold = (float) config('services.content_moderation.threshold', 0.70);

        $moderationCategory = $flaggedCategory
            ?? $highestScoreCategory['category']
            ?? 'safe';

        $moderationScore = $flaggedCategory
            ? (float) ($categoryScores[$flaggedCategory] ?? 1)
            : (float) ($highestScoreCategory['score'] ?? 0);

        $isFlagged = $flagged || $moderationScore >= $threshold;

        $moderationCategorySaved = $isFlagged
            ? $this->normalizeCategory($moderationCategory)
            : 'safe';

        $moderationScoreSaved = round($moderationScore, 4);

        Log::info('OpenAI moderation final decision.', [
            'openai_flagged' => $flagged,
            'threshold' => $threshold,
            'selected_category' => $moderationCategory,
            'selected_score' => $moderationScore,
            'is_flagged_by_system' => $isFlagged,
            'moderation_category_saved' => $moderationCategorySaved,
            'moderation_score_saved' => $moderationScoreSaved,
        ]);

        return [
            'is_flagged' => $isFlagged,
            'moderation_category' => $moderationCategorySaved,
            'moderation_score' => $moderationScoreSaved,
            'reason' => $isFlagged
                ? 'Potentially unsafe content detected by AI moderation.'
                : 'No unsafe content detected by AI moderation.',
            'raw_categories' => $categories,
            'raw_category_scores' => $categoryScores,
            'raw_category_applied_input_types' => $categoryAppliedInputTypes,
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
