<?php

namespace App\Services\Payment;

use App\Jobs\FulfillTransactionJob;
use App\Models\Content;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\Distribution\BundleZipService;
use App\Services\Editing\EditingService;
use RuntimeException;

class PaymentFulfillmentService
{
    public function __construct(
        private BundleZipService $bundleZipService,
        private EditingService $editingService
    ) {}

    public function markAsPaid(Transaction $transaction): Transaction
    {
        $transaction = DB::transaction(function () use ($transaction) {
            $transaction = Transaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($transaction->status === 'completed') {
                return $transaction;
            }

            if ($transaction->status !== 'processing') {
                $transaction->load([
                    'items.content',
                    'items.folder',
                ]);

                $this->assertItemsCanBeFulfilled($transaction);

                $paidAt = $transaction->paid_at ?? now();

                $transaction->update([
                    'status' => 'processing',
                    'payment_status' => 'success',
                    'paid_at' => $paidAt,
                    'invoice_number' => $transaction->invoice_number
                        ?? $this->generateInvoiceNumber($transaction, $paidAt),
                ]);

                $this->markSingleSaleContentAsSoldOut($transaction);
            }

            return $transaction;
        });

        if ($transaction->status === 'processing') {
            FulfillTransactionJob::dispatch($transaction->id);
        }

        return $transaction;
    }

    public function fulfill(Transaction $transaction): Transaction
    {
        $transaction = Transaction::query()
            ->with([
                'items.content',
                'items.folder',
                'items.preset',
            ])
            ->findOrFail($transaction->id);

        if ($transaction->status === 'completed') {
            return $transaction;
        }

        if ($transaction->status !== 'processing' || $transaction->payment_status !== 'success') {
            Log::warning('Fulfillment skipped: transaction is not in processing state.', [
                'transaction_id' => $transaction->id,
                'status' => $transaction->status,
            ]);

            return $transaction;
        }

        foreach ($transaction->items as $item) {
            if ($item->final_file_path) {
                continue;
            }

            $item->update([
                'final_file_path' => $this->buildFinalFile($transaction, $item),
            ]);
        }

        return DB::transaction(function () use ($transaction) {
            $transaction = Transaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($transaction->status === 'completed') {
                return $transaction;
            }

            $commissionRate = (float) config('marketplace.platform_commission_rate');

            if ($commissionRate < 0 || $commissionRate > 1) {
                throw new RuntimeException('Invalid platform commission rate.');
            }

            $paidAt = $transaction->paid_at ?? now();

            $transaction->load('items');

            foreach ($transaction->items as $item) {
                $itemUpdates = [];

                if ($item->commission_amount === null || $item->seller_amount === null) {
                    $price = round((float) $item->price_snapshot, 2);
                    $commission = round($price * $commissionRate, 2);

                    $itemUpdates['commission_amount'] = $commission;
                    $itemUpdates['seller_amount'] = round($price - $commission, 2);
                }

                if (! $item->download_available_at) {
                    $itemUpdates['download_available_at'] = $paidAt;
                }

                if (! $item->download_expires_at) {
                    $itemUpdates['download_expires_at'] = $paidAt->copy()->addDays(30);
                }

                if (! empty($itemUpdates)) {
                    $item->update($itemUpdates);
                }
            }

            $transaction->update(['status' => 'completed']);

            Log::info('Payment fulfillment completed.', [
                'transaction_id' => $transaction->id,
                'order_code' => $transaction->order_code,
            ]);

            return $transaction->fresh(['items']);
        });
    }

    private function buildFinalFile(Transaction $transaction, TransactionItem $item): string
    {
        if ($item->item_type === 'content') {
            if (! $item->content) {
                throw new RuntimeException('Content of the transaction item no longer exists.');
            }

            return $this->copyOriginalForBuyer($item);
        }

        if ($item->item_type === 'content_with_preset') {
            if (! $item->content) {
                throw new RuntimeException('Content of the transaction item no longer exists.');
            }

            if (! $item->preset_id || ! $item->preset) {
                throw new RuntimeException('Preset transaction item does not have a valid preset.');
            }

            return $this->editingService->generateFinal(
                $item->content,
                $item->preset,
                $item
            );
        }

        if ($item->item_type === 'bundle') {
            if (! $item->folder) {
                throw new RuntimeException('Bundle of the transaction item no longer exists.');
            }

            return $this->bundleZipService->generate($transaction, $item);
        }

        throw new RuntimeException("Unsupported transaction item type: {$item->item_type}");
    }

    private function markSingleSaleContentAsSoldOut(Transaction $transaction): void
    {
        foreach ($transaction->items as $item) {
            if (! $item->content_id) {
                continue;
            }

            Content::query()
                ->whereKey($item->content_id)
                ->where('sale_type', 'single_sale')
                ->where('sale_status', '!=', 'sold_out')
                ->update(['sale_status' => 'sold_out']);
        }
    }


    private function generateInvoiceNumber(Transaction $transaction, $paidAt): string
    {
        return sprintf(
            'INV-%s-%08d',
            $paidAt->format('Ymd'),
            $transaction->id
        );
    }

    private function copyOriginalForBuyer(TransactionItem $item): string
    {
        $sourcePath = $item->content->path_hi_res;

        if (! Storage::disk('local')->exists($sourcePath)) {
            throw new RuntimeException('Hi-res source image not found.');
        }

        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'jpg';

        $finalPath = sprintf(
            'transactions/%d/items/%d/final-original.%s',
            $item->transaction_id,
            $item->id,
            $extension
        );

        Storage::disk('local')->copy($sourcePath, $finalPath);

        return $finalPath;
    }

    private function assertItemsCanBeFulfilled(Transaction $transaction): void
    {
        foreach ($transaction->items as $item) {
            if (in_array($item->item_type, ['content', 'content_with_preset'], true)) {
                $content = Content::query()
                    ->whereKey($item->content_id)
                    ->lockForUpdate()
                    ->first();

                if (! $content) {
                    $this->blockFulfillment($transaction, $item, 'the content no longer exists');
                }

                if ($content->sale_type === 'single_sale') {
                    $soldToOthers = TransactionItem::query()
                        ->where('content_id', $content->id)
                        ->where('transaction_id', '!=', $transaction->id)
                        ->whereHas('transaction', function ($transactionQuery) {
                            $transactionQuery->where('status', ['processing', 'completed']);
                        })
                        ->exists();

                    if ($soldToOthers) {
                        $this->blockFulfillment($transaction, $item, 'the single-sale content was already sold');
                    }
                }
            }

            if ($item->item_type === 'bundle' && ! $item->folder) {
                $this->blockFulfillment($transaction, $item, 'the bundle no longer exists');
            }
        }
    }

    private function blockFulfillment(Transaction $transaction, TransactionItem $item, string $reason): never
    {
        Log::error('Payment fulfillment blocked.', [
            'transaction_id' => $transaction->id,
            'order_code' => $transaction->order_code,
            'transaction_item_id' => $item->id,
            'reason' => $reason,
        ]);

        throw new RuntimeException(
            "Fulfillment blocked for {$transaction->order_code}: {$reason}."
        );
    }
}
