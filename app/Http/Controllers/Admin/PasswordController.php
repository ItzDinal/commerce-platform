<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Fortify\UpdateUserPassword;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PasswordController extends Controller
{
    /**
     * Display the admin password update form.
     */
    public function edit(): View
    {
        return view('admin.password.edit');
    }

    /**
     * Update the authenticated admin's password.
     */
    public function update(
        Request $request,
        UpdateUserPassword $updater
    ): RedirectResponse {
        $updater->update(
            $request->user(),
            $request->all()
        );

        return redirect()
            ->route('admin.password.edit')
            ->with('success', 'Password updated successfully.');
    }
}