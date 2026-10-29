<?php

namespace App\Services\Checkout;

use App\Models\CartItem;
use App\Models\Content;
use App\Models\Folder;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\Storage;

class PurchaseValidationService
{
    public function contentError(Content $content, $user): ?string
    {
        if ($content->seller_id === $user->id) {
            return 'You cannot purchase your own content.';
        }

        if ($content->status !== 'active') {
            return 'This content is not currently available.';
        }

        if ($content->visibility !== 'public') {
            return 'This content is not publicly available.';
        }

        if ($content->sale_status !== 'available') {
            return 'This content is currently unavailable for purchase.';
        }

        if (
            $content->folder
            && $content->folder->is_bundle
            && ! $content->folder->allow_individual_sale
        ) {
            return 'This content is only available as part of its bundle.';
        }

        if (! $content->license) {
            return 'This content does not have a valid license.';
        }

        if ($this->hasActivePurchase($user, 'content_id', $content->id)) {
            return 'You already have an active purchase of this content.';
        }

        if (! Storage::disk('local')->exists($content->path_hi_res)) {
            return 'Hi-res source image not found.';
        }

        return null;
    }

    public function bundleError(Folder $folder, $user): ?string
    {
        if ($folder->seller_id === $user->id) {
            return 'You cannot purchase your own bundle.';
        }

        if (! $folder->is_bundle) {
            return 'This folder is not a bundle.';
        }

        if ($folder->status !== 'active') {
            return 'This bundle is not currently available.';
        }

        if ($folder->visibility !== 'public') {
            return 'This bundle is not publicly available.';
        }

        if ($folder->bundle_price === null) {
            return 'This bundle does not have a valid price.';
        }

        if (! $folder->license) {
            return 'This bundle does not have a valid license.';
        }

        if (! $folder->hasPurchasableBundleContents()) {
            return 'This bundle does not contain purchasable content.';
        }

        if ($this->hasActivePurchase($user, 'folder_id', $folder->id)) {
            return 'You already have an active purchase of this bundle.';
        }

        return null;
    }

    public function cartItemError(CartItem $item, $user): ?string
    {
        if ($item->item_type === 'content' || $item->item_type === 'content_with_preset') {
            if (! $item->content) {
                return 'Content no longer exists.';
            }

            $error = $this->contentError($item->content, $user);

            if ($error) {
                return $error;
            }

            if (
                $item->item_type === 'content_with_preset'
                && (! $item->preset || ! $item->preset->is_active)
            ) {
                return 'The selected preset is no longer available.';
            }

            return null;
        }

        if ($item->item_type === 'bundle') {
            if (! $item->folder) {
                return 'Bundle no longer exists.';
            }

            return $this->bundleError($item->folder, $user);
        }

        return 'Unsupported cart item type.';
    }

    public function currentPrice(CartItem $item): ?float
    {
        if ($item->item_type === 'bundle') {
            return $item->folder ? (float) $item->folder->bundle_price : null;
        }

        if ($item->item_type === 'content' || $item->item_type === 'content_with_preset') {
            return $item->content ? (float) $item->content->price : null;
        }

        return null;
    }

    public function itemName(CartItem $item): string
    {
        if ($item->item_type === 'bundle') {
            return $item->folder?->folder_name ?? 'A bundle';
        }

        return $item->content?->content_title ?? 'A content';
    }

    private function hasActivePurchase($user, string $column, int $productId): bool
    {
        return TransactionItem::query()
            ->where($column, $productId)
            ->activePurchaseOf($user->id)
            ->exists();
    }
}
