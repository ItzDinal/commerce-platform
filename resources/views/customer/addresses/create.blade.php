<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Address</title>
</head>
<body>
    <main>
        <h1>Add Address</h1>

        @if ($errors->any())
            <div>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('customer.addresses.store') }}"
        >
            @csrf

            <div>
                <label for="label">Label</label>
                <input
                    id="label"
                    type="text"
                    name="label"
                    value="{{ old('label') }}"
                >
            </div>

            <div>
                <label for="first_name">First Name</label>
                <input
                    id="first_name"
                    type="text"
                    name="first_name"
                    value="{{ old('first_name') }}"
                    required
                >
            </div>

            <div>
                <label for="last_name">Last Name</label>
                <input
                    id="last_name"
                    type="text"
                    name="last_name"
                    value="{{ old('last_name') }}"
                    required
                >
            </div>

            <div>
                <label for="company">Company</label>
                <input
                    id="company"
                    type="text"
                    name="company"
                    value="{{ old('company') }}"
                >
            </div>

            <div>
                <label for="address_line_1">Address Line 1</label>
                <input
                    id="address_line_1"
                    type="text"
                    name="address_line_1"
                    value="{{ old('address_line_1') }}"
                    required
                >
            </div>

            <div>
                <label for="address_line_2">Address Line 2</label>
                <input
                    id="address_line_2"
                    type="text"
                    name="address_line_2"
                    value="{{ old('address_line_2') }}"
                >
            </div>

            <div>
                <label for="city">City</label>
                <input
                    id="city"
                    type="text"
                    name="city"
                    value="{{ old('city') }}"
                    required
                >
            </div>

            <div>
                <label for="state">State</label>
                <input
                    id="state"
                    type="text"
                    name="state"
                    value="{{ old('state') }}"
                    required
                >
            </div>

            <div>
                <label for="postal_code">Postal Code</label>
                <input
                    id="postal_code"
                    type="text"
                    name="postal_code"
                    value="{{ old('postal_code') }}"
                    required
                >
            </div>

            <div>
                <label for="country">Country</label>
                <input
                    id="country"
                    type="text"
                    name="country"
                    value="{{ old('country') }}"
                    required
                >
            </div>

            <div>
                <label for="phone">Phone</label>
                <input
                    id="phone"
                    type="text"
                    name="phone"
                    value="{{ old('phone') }}"
                >
            </div>

            <button type="submit">
                Save Address
            </button>
        </form>
    </main>
</body>
</html>