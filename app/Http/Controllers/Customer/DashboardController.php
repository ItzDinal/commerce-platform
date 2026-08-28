<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $addresses = $user->addresses()
            ->latest()
            ->get();

        $recentOrders = $user->orders()
            ->latest()
            ->take(5)
            ->get();

        $wishlistItems = $user->wishlistItems()
            ->with('product')
            ->latest()
            ->take(5)
            ->get();

        return view('customer.dashboard', [
            'user' => $user,
            'addresses' => $addresses,
            'recentOrders' => $recentOrders,
            'wishlistItems' => $wishlistItems,
        ]);
    }
}