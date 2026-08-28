<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(Request $request): View
    {
        $addresses = $request->user()
            ->addresses()
            ->latest()
            ->get();

        return view('customer.addresses.index', compact('addresses'));
    }

    public function create(): View
    {
        return view('customer.addresses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAddress($request);

        $request->user()->addresses()->create($validated);

        return redirect()
            ->route('customer.addresses.index')
            ->with('success', 'Address added successfully.');
    }

    public function edit(Request $request, Address $address): View
    {
        $this->authorizeAddressOwnership($request, $address);

        return view('customer.addresses.edit', compact('address'));
    }

    public function update(Request $request, Address $address): RedirectResponse
    {
        $this->authorizeAddressOwnership($request, $address);

        $validated = $this->validateAddress($request);

        $address->update($validated);

        return redirect()
            ->route('customer.addresses.index')
            ->with('success', 'Address updated successfully.');
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        $this->authorizeAddressOwnership($request, $address);

        $address->delete();

        return redirect()
            ->route('customer.addresses.index')
            ->with('success', 'Address deleted successfully.');
    }

    protected function validateAddress(Request $request): array
    {
        return $request->validate([
            'label' => ['nullable', 'string', 'max:100'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'company' => ['nullable', 'string', 'max:150'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);
    }

    protected function authorizeAddressOwnership(Request $request, Address $address): void
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}