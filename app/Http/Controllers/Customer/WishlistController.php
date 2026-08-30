<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\WishlistItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Models\Cart;


class WishlistController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $request->user()
            ->wishlistItems()
            ->firstOrCreate([
                'product_id' => $validated['product_id'],
            ]);

        return back()->with('success', 'Product added to wishlist.');
    }

    public function destroy(
        Request $request,
        WishlistItem $wishlistItem
    ): RedirectResponse {
        abort_unless(
            $wishlistItem->user_id === $request->user()->id,
            403
        );

        $wishlistItem->delete();

        return back()->with('success', 'Product removed from wishlist.');
    }
    public function index(Request $request): View
    {
        $wishlistItems = $request->user()
            ->wishlistItems()
            ->with('product')
            ->latest()
            ->get();

        return view('customer.wishlist', [
            'wishlistItems' => $wishlistItems,
        ]);
    }

    
    public function moveToCart(
        Request $request,
        WishlistItem $wishlistItem
    ): RedirectResponse {
        abort_unless(
            $wishlistItem->user_id === $request->user()->id,
            403
        );

        $validated = $request->validate([
            'product_variant_id' => [
                'required',
                'exists:product_variants,id',
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $variant = ProductVariant::query()
            ->where('id', $validated['product_variant_id'])
            ->where('product_id', $wishlistItem->product_id)
            ->first();

        if (! $variant) {
            return back()->withErrors([
                'product_variant_id' => 'The selected variant does not belong to this product.',
            ]);
        }

        $cart = $request->user()->cart()->firstOrCreate([]);

        app(CartService::class)->addToCart(
            $cart,
            $variant,
            $validated['quantity']
        );

        $wishlistItem->delete();

        return back()->with(
            'success',
            'Product moved to cart.'
        );
    }
}