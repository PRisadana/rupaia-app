<?php

namespace App\Services;

use App\Models\Content;
use App\Models\Folder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Throwable;

class ContentUploadService
{
    public function __construct(
        private ImageHashService $imageHashService,
        private ImageModerationService $imageModerationService
    ) {}

    public function getFolderRules(Folder $folder, $licenseId, ?string $saleType, $price, bool $rejectSingleSale = true): array
    {
        if ($folder->is_bundle) {
            if (blank($folder->license_id)) {
                throw ValidationException::withMessages([
                    'folder_id' => 'This bundle folder does not have a license.',
                ]);
            }

            if ($rejectSingleSale && $saleType === 'single_sale') {
                throw ValidationException::withMessages([
                    'sale_type' => 'Single-sale content cannot be uploaded into a bundle folder.',
                ]);
            }

            $bundlePrice = $folder->allow_individual_sale ? $price : 0;

            return [
                'license_id' => $folder->license_id,
                'sale_type' => 'multi_sale',
                'price' => $bundlePrice,
                'visibility' => $folder->visibility,
            ];
        }

        if (blank($licenseId)) {
            throw ValidationException::withMessages([
                'license_id' => 'License is required when uploading content to a collection folder.',
            ]);
        }

        return [
            'license_id' => $licenseId,
            'sale_type' => $saleType ?? 'multi_sale',
            'price' => $price,
            'visibility' => $folder->visibility,
        ];
    }

    public function upload(int $sellerId, UploadedFile $file, array $data): Content
    {
        $fileName = Str::uuid() . '.' . $file->extension();

        $path_hi_res = $file->storeAs("content_file/{$sellerId}/hi_res", $fileName, 'local');
        $path_low_res = "content_file/{$sellerId}/low_res/{$fileName}";

        try {
            $hiResFullPath = Storage::disk('local')->path($path_hi_res);

            $imageValidation = $this->imageHashService->validateImage($hiResFullPath);
            $moderationResult = $this->imageModerationService->moderateImage(
                $hiResFullPath,
                $data['content_title'],
                $data['content_description']
            );

            $contentStatus = ($imageValidation['is_similar'] || $moderationResult['is_flagged'])
                ? 'pending_review'
                : 'active';

            $validationReasons = [];

            if ($imageValidation['is_similar']) {
                $validationReasons[] = $imageValidation['validation_reason'];
            }

            if ($moderationResult['is_flagged']) {
                $validationReasons[] = $moderationResult['reason'];
            }

            if (empty($validationReasons)) {
                $validationReasons[] = 'No similar or unsafe content detected.';
            }

            $validationReason = implode(' ', $validationReasons);

            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());
            $image->scale(width: 720);
            $imageWidth = $image->width();
            $watermarkPath = public_path('aset/watermark.png');
            if (file_exists($watermarkPath)) {
                $watermark = $manager->read($watermarkPath);
                $watermarkWidth = (int) ($imageWidth * 0.4);
                $watermark->scaleDown(width: $watermarkWidth);
                $image->place($watermark, 'center', 0, 0, 50);
            }

            Storage::disk('public')->put($path_low_res, (string) $image->encode());

            $contentData = [
                'seller_id' => $sellerId,
                'folder_id' => $data['folder_id'],
                'license_id' => $data['license_id'],
                'content_title' => $data['content_title'],
                'content_description' => $data['content_description'],
                'price' => $data['price'],
                'sale_type' => $data['sale_type'],
                'sale_status' => $data['sale_status'],
                'path_hi_res' => $path_hi_res,
                'path_low_res' => $path_low_res,
                'visibility' => $data['visibility'],
                'status' => $contentStatus,
                'perceptual_hash' => $imageValidation['perceptual_hash'],
                'similar_content_id' => $imageValidation['similar_content']?->id,
                'similarity_distance' => $imageValidation['similarity_distance'],
                'moderation_score' => $moderationResult['moderation_score'],
                'moderation_category' => $moderationResult['moderation_category'],
                'validation_reason' => $validationReason,
                'validated_at' => now(),
            ];

            $content = DB::transaction(function () use ($contentData, $data) {
                $content = Content::create($contentData);
                $content->tags()->sync($data['tag_ids']);

                return $content;
            });

            return $content;
        } catch (Throwable $e) {
            Storage::disk('local')->delete($path_hi_res);
            Storage::disk('public')->delete($path_low_res);

            throw $e;
        }
    }
}
