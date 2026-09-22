<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (! in_array($user->role, ['buyer', 'seller'], true)) {
            abort(403);
        }

        $cart = Cart::with([
            'items.content.user',
            'items.content.folder',
            'items.content.license',

            'items.folder.user',
            'items.folder.license',

            'items.preset',
        ])
            ->where('buyer_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->withErrors([
                    'checkout'
                    => 'Your cart is empty.',
                ]);
        }

        $validationErrors = $this->validateCartForCheckout($cart, $user);

        if (! empty($validationErrors)) {
            return redirect()
                ->route('cart.index')
                ->withErrors([
                    'checkout'
                    => implode(' ', $validationErrors),
                ]);
        }

        $total = $cart->items->sum(
            fn($item) => (float) $item->price_snapshot
        );

        return view('checkout.index', compact('cart', 'total'));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if (! in_array($user->role, ['buyer', 'seller'], true)) {
            abort(403);
        }

        $transaction = DB::transaction(
            function () use ($user) {
                $cart = Cart::where(
                    'buyer_id',
                    $user->id
                )
                    ->where(
                        'status',
                        'active'
                    )
                    ->lockForUpdate()
                    ->first();

                if (! $cart) {
                    throw ValidationException::withMessages([
                        'checkout'
                        => 'Active cart was not found.',
                    ]);
                }

                $cart->load([
                    'items.content.user',
                    'items.content.folder',
                    'items.content.license',

                    'items.folder.user',
                    'items.folder.license',

                    'items.preset',
                ]);


                if ($cart->items->isEmpty()) {
                    throw ValidationException::withMessages([
                        'checkout'
                        => 'Your cart is empty.',
                    ]);
                }

                $validationErrors = $this->validateCartForCheckout($cart, $user);

                if (! empty($validationErrors)) {
                    throw ValidationException::withMessages([
                        'checkout'
                        => implode(
                            ' ',
                            $validationErrors
                        ),
                    ]);
                }

                $total = $cart->items->sum(
                    fn($item) =>
                    (float) $item->price_snapshot
                );

                $transaction = Transaction::create([
                    'buyer_id' => $user->id,
                    'order_code' => $this->generateOrderCode(),
                    'status' => 'pending',
                    'total_amount' => $total,
                    'snap_token' => null,
                    'payment_status' => 'pending',
                    'paid_at' => null,
                    'invoice_number' => null,
                ]);

                foreach ($cart->items as $item) {
                    if ($item->item_type === 'content' || $item->item_type === 'content_with_preset') {
                        $product = $item->content;
                        $license = $product->license;
                        $transaction->items()->create([
                            'seller_id' => $product->seller_id,
                            'content_id' => $product->id,
                            'folder_id' => null,
                            'preset_id' => $item->preset_id,
                            'payout_id' => null,
                            'license_id' => $license->id,
                            'item_type' => $item->item_type,
                            'item_name_snapshot' => $product->content_title,
                            'license_name_snapshot' => $license->name,
                            'license_terms_snapshot' => $license->terms ?? '',
                            'price_snapshot' => $item->price_snapshot,
                            'final_file_path' => null,
                            'commission_amount' => null,
                            'seller_amount' => null,
                            'download_available_at' => null,
                            'download_expires_at' => null,
                            'download_count' => 0,
                        ]);
                    } elseif (
                        $item->item_type === 'bundle'
                    ) {
                        $folder = $item->folder;
                        $license = $folder->license;
                        $transaction->items()->create([
                            'seller_id' => $folder->seller_id,
                            'content_id' => null,
                            'folder_id' => $folder->id,
                            'preset_id' => null,
                            'payout_id' => null,
                            'license_id' => $license->id,
                            'item_type' => 'bundle',
                            'item_name_snapshot' => $folder->folder_name,
                            'license_name_snapshot' => $license->name,
                            'license_terms_snapshot' => $license->terms ?? '',
                            'price_snapshot' => $item->price_snapshot,
                            'final_file_path' => null,
                            'commission_amount' => null,
                            'seller_amount' => null,
                            'download_available_at' => null,
                            'download_expires_at' => null,
                            'download_count' => 0,
                        ]);
                    }
                }

                $cart->update(['status' => 'checked_out',]);

                return $transaction;
            }
        );


        return redirect()
            ->route(
                'transactions.show',
                $transaction
            )
            ->with(
                'success',
                'Checkout created successfully.'
            );
    }

    private function validateCartForCheckout(Cart $cart, $user): array
    {
        $errors = [];
        foreach ($cart->items as $item) {
            if ($item->item_type === 'content' || $item->item_type === 'content_with_preset') {
                $content = $item->content;
                if (! $content) {
                    $errors[] =
                        'One of the contents no longer exists.';
                    continue;
                }

                if ($content->seller_id === $user->id) {
                    $errors[] =
                        "{$content->content_title} is your own content.";
                }

                if ($content->status !== 'active') {
                    $errors[] =
                        "{$content->content_title} is no longer active.";
                }

                if ($content->visibility !== 'public') {
                    $errors[] =
                        "{$content->content_title} is no longer public.";
                }

                if ($content->sale_status !== 'available') {
                    $errors[] =
                        "{$content->content_title} is no longer available.";
                }

                if ($content->folder && $content->folder->is_bundle && ! $content->folder->allow_individual_sale) {
                    $errors[] =
                        "{$content->content_title} is only available through its bundle.";
                }

                if ((float) $content->price !== (float) $item->price_snapshot) {
                    $errors[] =
                        "The price of {$content->content_title} has changed.";
                }

                if (! $content->license) {
                    $errors[] =
                        "{$content->content_title} does not have a valid license.";
                }

                if ($item->item_type === 'content_with_preset' && (! $item->preset || ! $item->preset->is_active)) {
                    $errors[] =
                        "{$content->content_title} uses an unavailable preset.";
                }
            } elseif ($item->item_type === 'bundle') {
                $folder = $item->folder;
                if (! $folder) {
                    $errors[] =
                        'One of the bundles no longer exists.';
                    continue;
                }

                if ($folder->seller_id === $user->id) {
                    $errors[] =
                        "{$folder->folder_name} is your own bundle.";
                }

                if (! $folder->is_bundle) {
                    $errors[] =
                        "{$folder->folder_name} is no longer a bundle.";
                }

                if ($folder->status !== 'active') {
                    $errors[] =
                        "{$folder->folder_name} is no longer active.";
                }

                if ($folder->visibility !== 'public') {
                    $errors[] =
                        "{$folder->folder_name} is no longer public.";
                }

                if (! $folder->hasPurchasableBundleContents()) {
                    $errors[] =
                        "{$folder->folder_name} no longer contains purchasable content.";
                }

                if ((float) $folder->bundle_price !== (float) $item->price_snapshot) {
                    $errors[] =
                        "The price of {$folder->folder_name} has changed.";
                }

                if (! $folder->license) {
                    $errors[] =
                        "{$folder->folder_name} does not have a valid license.";
                }
            } else {
                $errors[] =
                    'Unsupported cart item type.';
            }
        }
        return $errors;
    }

    private function generateOrderCode(): string
    {
        do {
            $orderCode =
                'RPA-'
                . now()->format('YmdHis')
                . '-'
                . Str::upper(
                    Str::random(6)
                );
        } while (
            Transaction::where(
                'order_code',
                $orderCode
            )->exists()
        );
        return $orderCode;
    }
}
