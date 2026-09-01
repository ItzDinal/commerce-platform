<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Created</title>
</head>
<body>
    <main>
        <h1>Order created</h1>
        <p>Order number: {{ $order->order_number }}</p>
        <p>Status: {{ $order->status->label() }}</p>

        <h2>Purchased Items</h2>
        @foreach ($order->items as $item)
            <article>
                <h3>{{ $item->product_name }}</h3>
                <p>Variant: {{ $item->variant_name ?: 'Default' }}</p>
                <p>SKU: {{ $item->sku }}</p>
                <p>Quantity: {{ $item->quantity }}</p>
                <p>Unit price: {{ $item->unit_price }}</p>
                <p>Line total: {{ $item->line_total }}</p>
            </article>
        @endforeach

        <p>Subtotal: {{ $order->subtotal }}</p>
        <p>Shipping method: {{ $shippingMethodName }}</p>
        <p>Shipping fee: {{ $order->shipping_fee }}</p>
        <p>Total: {{ $order->total }}</p>
        <a href="{{ route('customer.dashboard') }}">Continue shopping</a>
    </main>
</body>
</html>
