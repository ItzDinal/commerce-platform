<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Addresses</title>
</head>
<body>
    <main>
        <h1>My Addresses</h1>

        @if (session('success'))
            <div>
                {{ session('success') }}
            </div>
        @endif

        <a href="{{ route('customer.addresses.create') }}">
            Add New Address
        </a>

        @forelse ($addresses as $address)
            <article>
                @if ($address->label)
                    <h2>{{ $address->label }}</h2>
                @endif

                <p>
                    {{ $address->first_name }} {{ $address->last_name }}
                </p>

                @if ($address->company)
                    <p>{{ $address->company }}</p>
                @endif

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

                @if ($address->phone)
                    <p>{{ $address->phone }}</p>
                @endif

                <p>
                    <a href="{{ route('customer.addresses.edit', $address) }}">
                        Edit
                    </a>
                </p>

                <form
                    method="POST"
                    action="{{ route('customer.addresses.destroy', $address) }}"
                    onsubmit="return confirm('Delete this address?');"
                >
                    @csrf
                    @method('DELETE')

                    <button type="submit">
                        Delete
                    </button>
                </form>
            </article>
        @empty
            <p>No saved addresses yet.</p>
        @endforelse
    </main>
</body>
</html>