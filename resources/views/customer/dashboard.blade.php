<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>
</head>
<body>
    <h1>My Account</h1>

    <section>
        <h2>Profile Summary</h2>

        <p>Name: {{ $user->name }}</p>
        <p>Email: {{ $user->email }}</p>

        <a href="{{ route('customer.profile') }}">Edit Profile</a>
    </section>
    <section>
        <h2>Recent Orders</h2>

        @if ($recentOrders->isEmpty())
            <p>You have no orders yet.</p>
        @else
            <ul>
                @foreach ($recentOrders as $order)
                    <li>
                        <strong>Order {{ $order->id }}</strong>

                        <span>
                            Status: {{ $order->status->label() }}
                        </span>

                        <span>
                            {{ $order->created_at->format('M d, Y') }}
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
    <section>
        <h2>Saved Addresses</h2>

        @if ($addresses->isEmpty())
            <p>You have no saved addresses.</p>
            <a href="{{ route('customer.addresses.create') }}">
                Add an address
            </a>
        @else
            @foreach ($addresses as $address)
                <article>
                    <h3>{{ $address->label ?: 'Address' }}</h3>

                    <p>
                        {{ $address->first_name }}
                        {{ $address->last_name }}
                    </p>

                    <p>{{ $address->address_line_1 }}</p>

                    @if ($address->address_line_2)
                        <p>{{ $address->address_line_2 }}</p>
                    @endif

                    <p>
                        {{ $address->city }},
                        {{ $address->state }}
                        {{ $address->postal_code }}
                    </p>

                    <p>{{ $address->country }}</p>

                    @if ($address->is_default_shipping)
                        <strong>Default Shipping</strong>
                    @endif

                    @if ($address->is_default_billing)
                        <strong>Default Billing</strong>
                    @endif
                </article>
            @endforeach

            <a href="{{ route('customer.addresses.index') }}">
                Manage Addresses
            </a>
        @endif
    </section>

    <section>
        <h2>Wishlist</h2>

        @if ($wishlistItems->isEmpty())
            <p>Your wishlist is empty.</p>
        @else
            <p>You have {{ $wishlistItems->count() }} item(s) in your wishlist.</p>

            <ul>
                @foreach ($wishlistItems as $wishlistItem)
                    <li>
                        {{ $wishlistItem->product->name }}
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <nav>
        <a href="{{ route('customer.profile') }}">Profile</a>
        <a href="{{ route('customer.addresses.index') }}">Addresses</a>
    </nav>
</body>
</html>
