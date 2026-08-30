<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Cart</title>
</head>
<body>
    <main>
        <h1>My Cart</h1>

        @if ($cart->items === [])
            <p>Your cart is empty.</p>
        @else
            @foreach ($cart->items as $item)
                <article>
                    <h2>{{ $item->productName }}</h2>

                    <p>
                        Quantity: {{ $item->quantity }}
                    </p>

                    <p>
                        Price: {{ $item->unitPrice }}
                    </p>

                    <p>
                        Line total: {{ $item->lineTotal }}
                    </p>
                </article>
            @endforeach

            <p>
                Total items: {{ $cart->totalItems }}
            </p>

            <p>
                Subtotal: {{ $cart->subtotal }}
            </p>
        @endif
    </main>
</body>
</html>