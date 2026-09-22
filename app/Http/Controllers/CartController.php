<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Content;
use App\Models\Folder;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $this->ensureBuyerAccess($user);

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

        // Jika belum punya cart, gunakan collection kosong.
        $items = $cart?->items ?? collect();

        $availabilityErrors = [];
        $currentPrices = [];
        $priceChanged = [];

        foreach ($items as $item) {
            if ($item->item_type === 'content' || $item->item_type === 'content_with_preset') {
                if (! $item->content) {
                    $availabilityErrors[$item->id]
                        = 'Content no longer exists.';
                    continue;
                }
                $error = $this->contentPurchaseError($item->content, $user);
                $currentPrice = (float) $item->content->price;
            } elseif ($item->item_type === 'bundle') {
                if (! $item->folder) {
                    $availabilityErrors[$item->id]
                        = 'Bundle no longer exists.';
                    continue;
                }

                $error = $this->bundlePurchaseError($item->folder, $user);
                $currentPrice =
                    (float) $item->folder->bundle_price;
            } else {
                $availabilityErrors[$item->id]
                    = 'Unsupported cart item type.';
                continue;
            }

            if ($error) {
                $availabilityErrors[$item->id]
                    = $error;
            }

            $currentPrices[$item->id] = $currentPrice;
            $priceChanged[$item->id] = (float) $item->price_snapshot !== $currentPrice;
        }

        $subtotal = $items->sum(
            fn($item) =>
            (float) $item->price_snapshot
        );

        $hasInvalidItem = ! empty($availabilityErrors);
        $hasPriceChange = collect($priceChanged)->contains(true);

        $canCheckout = $items->isNotEmpty()
            && ! $hasInvalidItem
            && ! $hasPriceChange;

        return view(
            'cart.index',
            compact(
                'cart',
                'items',
                'subtotal',
                'availabilityErrors',
                'currentPrices',
                'priceChanged',
                'canCheckout'
            )
        );
    }

    public function storeContent(Request $request, Content $content)
    {
        $user = $request->user();

        $this->ensureBuyerAccess($user);

        $content->load('folder');

        $purchaseError = $this->contentPurchaseError($content, $user);

        if ($purchaseError) {
            return back()
                ->withErrors([
                    'cart' => $purchaseError,
                ]);
        }

        $cart = Cart::firstOrCreate(
            [
                'buyer_id' => $user->id,
                'status' => 'active',
            ]
        );

        $duplicateExists = $cart->items()
            ->where('item_type', 'content')
            ->where('content_id', $content->id)
            ->exists();

        if ($duplicateExists) {
            return back()
                ->withErrors([
                    'cart'
                    => 'This content is already in your cart.',
                ]);
        }

        $cart->items()->create([
            'content_id' => $content->id,
            'folder_id' => null,
            'preset_id' => null,
            'item_type' => 'content',
            'price_snapshot' => $content->price,
        ]);

        return back()
            ->with(
                'success',
                'Content added to cart successfully.'
            );
    }

    public function storeBundle(Request $request, Folder $folder)
    {
        $user = $request->user();

        $this->ensureBuyerAccess($user);

        $purchaseError = $this->bundlePurchaseError($folder, $user);

        if ($purchaseError) {
            return back()
                ->withErrors([
                    'cart' => $purchaseError,
                ]);
        }

        $cart = Cart::firstOrCreate(
            [
                'buyer_id' => $user->id,
                'status' => 'active',
            ]
        );

        $duplicateExists = $cart->items()
            ->where('item_type', 'bundle')
            ->where('folder_id', $folder->id)
            ->exists();

        if ($duplicateExists) {
            return back()
                ->withErrors([
                    'cart'
                    => 'This bundle is already in your cart.',
                ]);
        }

        $cart->items()->create([
            'content_id' => null,
            'folder_id' => $folder->id,
            'preset_id' => null,
            'item_type' => 'bundle',
            'price_snapshot' => $folder->bundle_price,
        ]);

        return back()
            ->with(
                'success',
                'Bundle added to cart successfully.'
            );
    }

    public function refreshPrice(Request $request, CartItem $cartItem)
    {
        $user = $request->user();

        $this->ensureBuyerAccess($user);

        $cartItem->load([
            'cart',
            'content.folder',
            'folder',
        ]);

        if ($cartItem->cart->buyer_id !== $user->id || $cartItem->cart->status !== 'active') {
            abort(403);
        }

        if ($cartItem->item_type === 'content' || $cartItem->item_type === 'content_with_preset') {
            if (! $cartItem->content) {
                return back()
                    ->withErrors([
                        'cart'
                        => 'Content no longer exists.',
                    ]);
            }

            $error = $this->contentPurchaseError(
                $cartItem->content,
                $user
            );
            if ($error) {
                return back()
                    ->withErrors([
                        'cart' => $error,
                    ]);
            }
            $currentPrice = $cartItem->content->price;
        } elseif ($cartItem->item_type === 'bundle') {
            if (! $cartItem->folder) {
                return back()
                    ->withErrors([
                        'cart'
                        => 'Bundle no longer exists.',
                    ]);
            }
            $error = $this->bundlePurchaseError(
                $cartItem->folder,
                $user
            );
            if ($error) {
                return back()
                    ->withErrors([
                        'cart' => $error,
                    ]);
            }
            $currentPrice = $cartItem->folder->bundle_price;
        } else {
            return back()
                ->withErrors([
                    'cart'
                    => 'Unsupported cart item type.',
                ]);
        }

        $cartItem->update([
            'price_snapshot' => $currentPrice,
        ]);

        return redirect()
            ->route('cart.index')
            ->with(
                'success',
                'Cart price has been updated.'
            );
    }

    public function destroy(Request $request, CartItem $cartItem)
    {
        $user = $request->user();

        $this->ensureBuyerAccess($user);

        $cartItem->load('cart');

        if ($cartItem->cart->buyer_id !== $user->id || $cartItem->cart->status !== 'active') {
            abort(403);
        }

        $cartItem->delete();

        return redirect()
            ->route('cart.index')
            ->with(
                'success',
                'Item removed from cart.'
            );
    }


    private function ensureBuyerAccess($user): void
    {
        if (! in_array(
            $user->role,
            ['buyer', 'seller'],
            true
        )) {
            abort(403);
        }
    }

    private function contentPurchaseError(Content $content, $user): ?string
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

        return null;
    }

    private function bundlePurchaseError(Folder $folder, $user): ?string
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

        if (! $folder->hasPurchasableBundleContents()) {
            return 'This bundle does not contain purchasable content.';
        }

        return null;
    }
}
