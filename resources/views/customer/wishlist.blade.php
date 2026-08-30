<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist</title>
</head>
<body>
    <main>
        <h1>My Wishlist</h1>

        @if (session('success'))
            <div>
                {{ session('success') }}
            </div>
        @endif

        @if ($wishlistItems->isEmpty())
            <p>Your wishlist is empty.</p>
        @else
            @foreach ($wishlistItems as $wishlistItem)
                <article>
                    <h2>{{ $wishlistItem->product->name }}</h2>

                    <form
                        method="POST"
                        action="{{ route('customer.wishlist.destroy', $wishlistItem) }}"
                    >
                        @csrf
                        @method('DELETE')

                        <button type="submit">
                            Remove
                        </button>
                    </form>
                </article>
            @endforeach
        @endif
    </main>
</body>
</html>