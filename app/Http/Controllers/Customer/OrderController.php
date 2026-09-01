<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display the customer's orders.
     */
    public function index(Request $request): View
    {
        $orders = $request->user()
            ->orders()
            ->with('items')
            ->latest()
            ->paginate(10);

        return view('customer.orders.index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Display a single customer order.
     */
    public function show(Request $request, Order $order): View
    {
        abort_unless(
            $order->user_id === $request->user()->id,
            403
        );

        $order->load([
            'items',
            'shippingAddress',
            'billingAddress',
        ]);

        return view('customer.orders.show', [
            'order' => $order,
        ]);
    }
}