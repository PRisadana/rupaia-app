<?php

namespace App\Http\Controllers\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Content;
use App\Models\Folder;
use App\Models\Preset;
use App\Services\Checkout\PurchaseValidationService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CartController extends Controller
{
    public function __construct(
        private PurchaseValidationService $purchaseValidationService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

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

        $items = $cart?->items ?? collect();

        $availabilityErrors = [];
        $currentPrices = [];
        $priceChanged = [];

        foreach ($items as $item) {
            $error = $this->purchaseValidationService->cartItemError($item, $user);

            if ($error) {
                $availabilityErrors[$item->id] = $error;
            }

            $currentPrice = $this->purchaseValidationService->currentPrice($item);

            if ($currentPrice === null) {
                continue;
            }

            $currentPrices[$item->id] = $currentPrice;
            $priceChanged[$item->id] = (float) $item->price_snapshot !== $currentPrice;
        }

        $subtotal = $items->sum(
            fn($item) => (float) $item->price_snapshot
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

        $content->load('folder');

        $purchaseError = $this->purchaseValidationService->contentError($content, $user);

        $validated = $request->validate([
            'preset_id' => [
                'nullable',
                'integer',
            ],
        ]);

        $preset = null;

        if (! empty($validated['preset_id'])) {
            $preset = Preset::query()
                ->whereKey($validated['preset_id'])
                ->where('is_active', true)
                ->first();

            if (! $preset) {
                return back()
                    ->withErrors([
                        'cart' => 'The selected preset is no longer available.',
                    ]);
            }
        }

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
            ->where('content_id', $content->id)
            ->whereIn('item_type', ['content', 'content_with_preset'])
            ->exists();

        if ($duplicateExists) {
            return back()
                ->withErrors([
                    'cart' => 'This content is already in your cart.',
                ]);
        }

        $cart->items()->create([
            'content_id' => $content->id,
            'folder_id' => null,
            'preset_id' => $preset?->id,
            'item_type' => $preset ? 'content_with_preset' : 'content',
            'price_snapshot' => $content->price,
        ]);

        return back()
            ->with('success', 'Content added to cart successfully.');
    }

    public function storeBundle(Request $request, Folder $folder)
    {
        $user = $request->user();

        $purchaseError = $this->purchaseValidationService->bundleError($folder, $user);

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
                    'cart' => 'This bundle is already in your cart.',
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
            ->with('success', 'Bundle added to cart successfully.');
    }

    public function refreshPrice(Request $request, CartItem $cartItem)
    {
        $user = $request->user();

        $cartItem->load([
            'cart',
            'content.folder',
            'content.license',
            'folder.license',
            'preset',
        ]);

        if ($cartItem->cart->buyer_id !== $user->id || $cartItem->cart->status !== 'active') {
            abort(403);
        }

        $error = $this->purchaseValidationService->cartItemError($cartItem, $user);

        if ($error) {
            return back()
                ->withErrors([
                    'cart' => $error,
                ]);
        }

        $cartItem->update([
            'price_snapshot' => $this->purchaseValidationService->currentPrice($cartItem),
        ]);

        return redirect()
            ->route('cart.index')
            ->with('success', 'Cart price has been updated.');
    }

    public function destroy(Request $request, CartItem $cartItem)
    {
        $user = $request->user();

        $cartItem->load('cart');

        if ($cartItem->cart->buyer_id !== $user->id || $cartItem->cart->status !== 'active') {
            abort(403);
        }

        $cartItem->delete();

        return redirect()
            ->route('cart.index')
            ->with('success', 'Item removed from cart.');
    }
}
