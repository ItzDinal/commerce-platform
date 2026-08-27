<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    /**
     * Display the authenticated customer's profile.
     */
    public function show(): View|RedirectResponse
    {
        return view('customer.profile');
    }
}