<?php

namespace App\Http\Controllers\Customer;

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Actions\Fortify\UpdateUserPassword;

class ProfileController extends Controller
{
    /**
     * Display the authenticated customer's profile.
     */
    public function show(): View
    {
        return view('customer.profile');
    }

    /**
     * Update the authenticated customer's profile.
     */
    public function update(
        Request $request,
        UpdateUserProfileInformation $updater
    ): RedirectResponse {
        $updater->update(
            $request->user(),
            $request->only(['name', 'email'])
        );

        return redirect()
            ->route('customer.profile')
            ->with('success', 'Profile updated successfully.');
    }
    public function updatePassword(
    Request $request,
    UpdateUserPassword $updater
    ): RedirectResponse {
        $updater->update(
            $request->user(),
            $request->all()
        );

        return redirect()
            ->route('customer.profile')
            ->with('success', 'Password updated successfully.');
    }
}