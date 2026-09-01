<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
</head>
<body>
    <main>
        <h1>Checkout</h1>

        @if ($errors->any())
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('customer.checkout.store') }}">
            @csrf

            {{-- Customer Information --}}
            <section>
                <h2>Customer Information</h2>

                <label for="name">Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $customer->name) }}"
                    required
                >
                @error('name')
                    <p>{{ $message }}</p>
                @enderror

                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $customer->email) }}"
                    required
                >
                @error('email')
                    <p>{{ $message }}</p>
                @enderror

                <label for="phone">Phone</label>
                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    value="{{ old('phone', $customer->phone) }}"
                    placeholder="+94 77 123 4567"
                >
                @error('phone')
                    <p>{{ $message }}</p>
                @enderror
            </section>

            {{-- Shipping Address --}}
            <section>
                <h2>Shipping Address</h2>

                @forelse ($addresses as $address)
                    <label>
                        <input
                            type="radio"
                            name="shipping_address_id"
                            value="{{ $address->id }}"
                            @checked(old('shipping_address_id') === $address->id)
                            required
                        >

                        {{ $address->first_name }}
                        {{ $address->last_name }},
                        {{ $address->address_line_1 }},
                        {{ $address->city }}
                    </label>
                @empty
                    <p>No saved addresses available.</p>
                @endforelse

                @error('shipping_address_id')
                    <p>{{ $message }}</p>
                @enderror
            </section>

            {{-- Billing Address --}}
            <section>
                <h2>Billing Address</h2>

                <label for="same_as_shipping">
                    <input
                        type="checkbox"
                        id="same_as_shipping"
                        name="same_as_shipping"
                        value="1"
                        @checked(old('same_as_shipping', true))
                    >

                    Billing address same as shipping address
                </label>

                <div id="billing-address-options">
                    <h3>Select Billing Address</h3>

                    @forelse ($addresses as $address)
                        <label>
                            <input
                                type="radio"
                                name="billing_address_id"
                                value="{{ $address->id }}"
                                @checked(old('billing_address_id') === $address->id)
                            >

                            {{ $address->first_name }}
                            {{ $address->last_name }},
                            {{ $address->address_line_1 }},
                            {{ $address->city }}
                        </label>
                    @empty
                        <p>No saved billing addresses available.</p>
                    @endforelse

                    @error('billing_address_id')
                        <p>{{ $message }}</p>
                    @enderror
                </div>
            </section>

            {{-- Order Items --}}
            <section>
                <h2>Order Items</h2>

                @foreach ($quote['items'] as $item)
                    <article>
                        <h3>{{ $item['productName'] }}</h3>

                        <p>Variant: {{ $item['variantName'] ?: 'Default' }}</p>
                        <p>SKU: {{ $item['sku'] }}</p>
                        <p>Quantity: {{ $item['quantity'] }}</p>
                        <p>Unit price: {{ $item['unitPrice'] }}</p>
                        <p>Line total: {{ $item['lineTotal'] }}</p>
                    </article>
                @endforeach
            </section>

            {{-- Shipping Method --}}
            <section>
                <h2>Shipping Method</h2>

                <label>
                    <input
                        type="radio"
                        name="shipping_method"
                        value="{{ $quote['shippingMethod'] }}"
                        @checked(old('shipping_method', $quote['shippingMethod']) === $quote['shippingMethod'])
                        required
                    >
                    {{ $quote['shippingMethodName'] }}
                    ({{ $quote['shippingFee'] }} LKR)
                </label>

                @error('shipping_method')
                    <p>{{ $message }}</p>
                @enderror
            </section>

            {{-- Order Summary --}}
            <section>
                <h2>Order Summary</h2>

                <p>Subtotal: {{ $quote['subtotal'] }}</p>
                <p>Shipping method: {{ $quote['shippingMethodName'] }}</p>
                <p>Shipping fee: {{ $quote['shippingFee'] }}</p>
                <p>Total: {{ $quote['total'] }}</p>
            </section>

            @error('checkout')
                <p>{{ $message }}</p>
            @enderror

            @error('cart')
                <p>{{ $message }}</p>
            @enderror

            <button type="submit">
                Place Order
            </button>
        </form>
    </main>

    <script>
        const sameAsShipping =
            document.getElementById('same_as_shipping');

        const billingOptions =
            document.getElementById('billing-address-options');

        const shippingInputs =
            document.querySelectorAll(
                'input[name="shipping_address_id"]'
            );

        const billingInputs =
            document.querySelectorAll(
                'input[name="billing_address_id"]'
            );

        function updateBillingAddress() {
            if (!sameAsShipping || !billingOptions) {
                return;
            }

            if (sameAsShipping.checked) {
                billingOptions.style.display = 'none';

                const selectedShipping =
                    document.querySelector(
                        'input[name="shipping_address_id"]:checked'
                    );

                billingInputs.forEach(input => {
                    input.disabled = true;

                    if (selectedShipping) {
                        input.checked =
                            input.value === selectedShipping.value;
                    }
                });
            } else {
                billingOptions.style.display = 'block';

                billingInputs.forEach(input => {
                    input.disabled = false;
                });
            }
        }

        if (sameAsShipping) {
            sameAsShipping.addEventListener(
                'change',
                updateBillingAddress
            );

            shippingInputs.forEach(input => {
                input.addEventListener(
                    'change',
                    updateBillingAddress
                );
            });

            updateBillingAddress();
        }
    </script>
</body>
</html>
