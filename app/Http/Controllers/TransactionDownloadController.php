<?php

namespace App\Http\Controllers;

use App\Models\TransactionItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Filesystem\FilesystemAdapter;

class TransactionDownloadController extends Controller
{
    public function download(Request $request, Transaction $transaction, $transactionItem)
    {
        if (! $transaction || $transaction->buyer_id !== $request->user()->id) {
            abort(403);
        }

        $transactionItem = $transaction
            ->items()
            ->whereKey($transactionItem)
            ->firstOrFail();

        if ($transaction->status !== 'completed' || $transaction->payment_status !== 'success') {
            return back()
                ->withErrors([
                    'download'
                    => 'This transaction has not been completed.',
                ]);
        }

        if (! $transactionItem->download_available_at) {
            return back()
                ->withErrors([
                    'download'
                    => 'This file is not available for download yet.',
                ]);
        }

        if (! $transactionItem->download_expires_at || now()->greaterThan($transactionItem->download_expires_at)) {
            return back()
                ->withErrors([
                    'download'
                    => 'The download period has expired.',
                ]);
        }

        if (! $transactionItem->final_file_path || ! Storage::disk('local')->exists($transactionItem->final_file_path)) {
            return back()
                ->withErrors([
                    'download'
                    => 'The purchased file is unavailable.',
                ]);
        }

        $transactionItem->increment('download_count');

        $extension = pathinfo($transactionItem->final_file_path, PATHINFO_EXTENSION);

        $downloadName = Str::slug($transactionItem->item_name_snapshot);

        if ($extension) {
            $downloadName .=
                '.' . $extension;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        return $disk->download(
            $transactionItem->final_file_path,
            $downloadName
        );
    }
}
