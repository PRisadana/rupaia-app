<?php

namespace App\Services;

use App\Models\Payout;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserDeletionService
{
    public function blockReason(User $user): ?string
    {
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return 'The last admin account cannot be deleted.';
        }

        if ($user->transactions()->exists()) {
            return 'This account has purchase history and cannot be deleted.';
        }

        if ($user->soldTransactionItems()->exists()) {
            return 'This account has sales history and cannot be deleted.';
        }

        if (Payout::where('seller_id', $user->id)->exists()) {
            return 'This account has payout history and cannot be deleted.';
        }

        return null;
    }

    public function delete(User $user): void
    {
        $privatePaths = $user->kycSubmissions()
            ->pluck('id_card_path')
            ->all();

        $publicPaths = $user->showcaseItems()
            ->whereNotNull('custom_path')
            ->pluck('custom_path')
            ->all();

        if ($user->profile_photo_path) {
            $publicPaths[] = $user->profile_photo_path;
        }

        DB::transaction(function () use ($user) {
            foreach ($user->contents()->get() as $content) {
                $content->delete();
            }

            $user->delete();
        });

        Storage::disk('private')->delete($privatePaths);
        Storage::disk('public')->delete($publicPaths);
    }
}
