<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        private CheckoutService $checkoutService
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $cart = $request->user()->persistentCart();

        if (! $cart->items()->exists()) {
            return redirect()
                ->route('customer.cart.index')
                ->withErrors(['cart' => 'Your cart is empty.']);
        }

        return view('customer.checkout', [
            'customer' => $request->user(),
            'quote' => $this->checkoutService->quote($cart),
            'addresses' => $request->user()->addresses()->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $customer = $request->user();

        $request->merge([
            'name' => $request->input('name', $customer->name),
            'email' => $request->input('email', $customer->email),
            'phone' => $request->input('phone', $customer->phone),
            'shipping_method' => $request->input(
                'shipping_method',
                CheckoutService::STANDARD_SHIPPING_METHOD
            ),
        ]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($customer->id),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^\\+?[0-9 ()-]+$/',
            ],

            'shipping_address_id' => [
                'required',
                'ulid',
                Rule::exists('addresses', 'id')
                    ->where(fn ($query) => $query->where(
                        'user_id',
                        $request->user()->id
                    )),
            ],

            'billing_address_id' => [
                'nullable',
                'ulid',
                Rule::exists('addresses', 'id')
                    ->where(fn ($query) => $query->where(
                        'user_id',
                        $request->user()->id
                    )),
            ],

            'same_as_shipping' => [
                'nullable',
                'boolean',
            ],

            'shipping_method' => [
                'required',
                Rule::in([CheckoutService::STANDARD_SHIPPING_METHOD]),
            ],
        ]);

        if (! $customer->persistentCart()->items()->exists()) {
            return back()->withErrors([
                'cart' => 'Your cart is empty.',
            ]);
        }

        try {
            $billingAddressId = ($validated['same_as_shipping'] ?? false)
                ? null
                : ($validated['billing_address_id'] ?? null);

            $order = $this->checkoutService->createOrder(
                $customer,
                $validated['shipping_address_id'],
                [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                ],
                $billingAddressId,
                $validated['shipping_method']
            );
        } catch (RuntimeException $exception) {
            return back()->withErrors([
                'checkout' => $exception->getMessage(),
            ]);
        }

        return redirect()->route(
            'customer.checkout.success',
            $order
        );
    }

    public function success(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load('items');

        return view('customer.checkout-success', [
            'order' => $order,
            'shippingMethodName' => $this->checkoutService
                ->shippingMethodName($order->shipping_method),
        ]);
    }
}
