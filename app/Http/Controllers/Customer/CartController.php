<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\CartItem;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService
    ) {
    }

    public function index(Request $request): View
    {
        $cart = $request->user()->persistentCart();

        $cartData = $this->cartService->getCartData($cart);

        return view('customer.cart', [
            'cart' => $cartData,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
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

        $variant = ProductVariant::findOrFail(
            $validated['product_variant_id']
        );

        $cart = $request->user()->persistentCart();

        try {
            $this->cartService->addToCart(
                $cart,
                $variant,
                $validated['quantity']
            );
        } catch (\RuntimeException $e) {
            return back()->withErrors([
                'quantity' => $e->getMessage(),
            ]);
        }

        return back()->with(
            'success',
            'Product added to cart.'
        );
    }

    public function destroy(Request $request, CartItem $item): RedirectResponse
    {
        abort_unless(
            $item->cart->user_id === $request->user()->id,
            403
        );

        $this->cartService->removeItem($item);

        return back()->with(
            'success',
            'Product removed from cart.'
        );
    }
}
