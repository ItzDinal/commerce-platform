@extends('layouts.customer')

@section('title', 'Order ' . $order->order_number)

@section('content')
<div class="container mx-auto px-4 py-8">

    {{-- Back --}}
    <div class="mb-6">
        <a
            href="{{ route('customer.orders.index') }}"
            class="text-sm font-medium text-gray-600 hover:text-gray-900"
        >
            ← Back to My Orders
        </a>
    </div>

    {{-- Header --}}
    <div class="mb-8">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">

            <div>
                <p class="text-sm text-gray-500">
                    Order Number
                </p>

                <h1 class="mt-1 text-2xl font-semibold text-gray-900">
                    {{ $order->order_number }}
                </h1>

                <p class="mt-2 text-sm text-gray-500">
                    Placed on {{ $order->created_at->format('d M Y, h:i A') }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">
                    Current Status
                </p>

                <p class="mt-1 font-semibold text-gray-900">
                    {{ $order->status->label() }}
                </p>
            </div>

        </div>

    </div>

    {{-- Tracking --}}
    <section class="mb-8 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">

        <h2 class="text-lg font-semibold text-gray-900">
            Track Your Order
        </h2>

        <p class="mt-1 text-sm text-gray-500">
            Follow the progress of your order from placement to delivery.
        </p>

        @php
            $statuses = [
                \App\Enums\OrderStatus::PENDING,
                \App\Enums\OrderStatus::CONFIRMED,
                \App\Enums\OrderStatus::PROCESSING,
                \App\Enums\OrderStatus::PACKED,
                \App\Enums\OrderStatus::SHIPPED,
                \App\Enums\OrderStatus::OUT_FOR_DELIVERY,
                \App\Enums\OrderStatus::DELIVERED,
            ];

            $currentIndex = array_search(
                $order->status,
                $statuses,
                true
            );

            if ($currentIndex === false) {
                $currentIndex = 0;
            }
        @endphp

        <div class="mt-6 overflow-x-auto pb-2">

            <div class="flex min-w-max items-start">

                @foreach ($statuses as $index => $status)

                    @php
                        $isCompleted = $index <= $currentIndex;
                        $isCurrent = $index === $currentIndex;
                    @endphp

                    <div class="flex items-start">

                        <div class="flex w-28 flex-col items-center text-center">

                            {{-- Status Circle --}}
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full border-2
                                {{ $isCompleted
                                    ? 'border-gray-900 bg-gray-900 text-white'
                                    : 'border-gray-300 bg-white text-gray-400' }}"
                            >

                                @if ($isCompleted)

                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                        class="h-5 w-5"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M16.704 4.153a.75.75 0 01.143 1.052l-7.25 9a.75.75 0 01-1.127.075l-4.25-4.5a.75.75 0 111.09-1.03l3.662 3.88 6.695-8.31a.75.75 0 011.037-.167z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>

                                @else

                                    <span class="h-2 w-2 rounded-full bg-current"></span>

                                @endif

                            </div>

                            {{-- Status --}}
                            <p
                                class="mt-3 text-xs leading-4
                                {{ $isCurrent
                                    ? 'font-semibold text-gray-900'
                                    : 'text-gray-500' }}"
                            >
                                {{ $status->label() }}
                            </p>

                        </div>

                        @if (!$loop->last)

                            <div
                                class="mt-5 h-0.5 w-10
                                {{ $index < $currentIndex
                                    ? 'bg-gray-900'
                                    : 'bg-gray-300' }}"
                            ></div>

                        @endif

                    </div>

                @endforeach

            </div>

        </div>

    </section>

    {{-- Order Items --}}
    <section class="mb-8 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-6 py-5">
            <h2 class="text-lg font-semibold text-gray-900">
                Order Items
            </h2>
        </div>

        <div class="divide-y divide-gray-200">

            @foreach ($order->items as $item)

                <div class="p-6">

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                        <div>

                            <h3 class="font-medium text-gray-900">
                                {{ $item->product_name }}
                            </h3>

                            @if ($item->variant_name)
                                <p class="mt-1 text-sm text-gray-500">
                                    Variant: {{ $item->variant_name }}
                                </p>
                            @endif

                            <p class="mt-1 text-sm text-gray-500">
                                SKU: {{ $item->sku }}
                            </p>

                        </div>

                        <div class="sm:text-right">

                            <p class="text-sm text-gray-500">
                                Quantity: {{ $item->quantity }}
                            </p>

                            <p class="mt-1 text-sm text-gray-500">
                                Unit Price: LKR {{ number_format($item->unit_price) }}
                            </p>

                            <p class="mt-1 font-semibold text-gray-900">
                                Line Total: LKR {{ number_format($item->line_total) }}
                            </p>

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

    </section>

    {{-- Addresses --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Shipping Address --}}
        <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">

            <h2 class="text-lg font-semibold text-gray-900">
                Shipping Address
            </h2>

            @if ($order->shippingAddress)

                <div class="mt-4 text-sm leading-6 text-gray-600">

                    <p class="font-medium text-gray-900">
                        {{ $order->shippingAddress->first_name }}
                        {{ $order->shippingAddress->last_name }}
                    </p>

                    <p>
                        {{ $order->shippingAddress->address_line_1 }}
                    </p>

                    @if ($order->shippingAddress->address_line_2)
                        <p>
                            {{ $order->shippingAddress->address_line_2 }}
                        </p>
                    @endif

                    <p>
                        {{ $order->shippingAddress->city }},
                        {{ $order->shippingAddress->state }}
                        {{ $order->shippingAddress->postal_code }}
                    </p>

                    <p>
                        {{ $order->shippingAddress->country }}
                    </p>

                </div>

            @else

                <p class="mt-4 text-sm text-gray-500">
                    Shipping address is no longer available.
                </p>

            @endif

        </section>

        {{-- Billing Address --}}
        <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">

            <h2 class="text-lg font-semibold text-gray-900">
                Billing Address
            </h2>

            @if ($order->billingAddress)

                <div class="mt-4 text-sm leading-6 text-gray-600">

                    <p class="font-medium text-gray-900">
                        {{ $order->billingAddress->first_name }}
                        {{ $order->billingAddress->last_name }}
                    </p>

                    <p>
                        {{ $order->billingAddress->address_line_1 }}
                    </p>

                    @if ($order->billingAddress->address_line_2)
                        <p>
                            {{ $order->billingAddress->address_line_2 }}
                        </p>
                    @endif

                    <p>
                        {{ $order->billingAddress->city }},
                        {{ $order->billingAddress->state }}
                        {{ $order->billingAddress->postal_code }}
                    </p>

                    <p>
                        {{ $order->billingAddress->country }}
                    </p>

                </div>

            @else

                <p class="mt-4 text-sm text-gray-500">
                    Billing address is no longer available.
                </p>

            @endif

        </section>

    </div>

    {{-- Shipping + Totals --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Shipping Method --}}
        <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">

            <h2 class="text-lg font-semibold text-gray-900">
                Shipping Method
            </h2>

            <p class="mt-3 text-sm text-gray-600">
                @if ($order->shipping_method === 'standard')
                    Standard Shipping
                @else
                    {{ ucfirst(str_replace('_', ' ', $order->shipping_method)) }}
                @endif
            </p>

        </section>

        {{-- Order Summary --}}
        <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">

            <h2 class="text-lg font-semibold text-gray-900">
                Order Summary
            </h2>

            <dl class="mt-4 space-y-3 text-sm">

                <div class="flex justify-between">
                    <dt class="text-gray-500">
                        Subtotal
                    </dt>

                    <dd class="font-medium text-gray-900">
                        LKR {{ number_format($order->subtotal) }}
                    </dd>
                </div>

                <div class="flex justify-between">
                    <dt class="text-gray-500">
                        Shipping
                    </dt>

                    <dd class="font-medium text-gray-900">
                        LKR {{ number_format($order->shipping_fee) }}
                    </dd>
                </div>

                <div class="border-t border-gray-200 pt-3">
                    <div class="flex justify-between">

                        <dt class="font-semibold text-gray-900">
                            Total
                        </dt>

                        <dd class="font-semibold text-gray-900">
                            LKR {{ number_format($order->total) }}
                        </dd>

                    </div>
                </div>

            </dl>

        </section>

    </div>

    {{-- Actions --}}
    <div class="mt-8 flex flex-col gap-3 border-t border-gray-200 pt-6 sm:flex-row">

        <a
            href="{{ route('customer.orders.index') }}"
            class="inline-flex items-center justify-center rounded-md border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
        >
            Back to My Orders
        </a>

        {{-- Customer cancellation is not implemented.
             This will eventually point to WhatsApp Business. --}}
        <a
            href="#"
            onclick="return false;"
            class="inline-flex cursor-not-allowed items-center justify-center rounded-md border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-400"
            aria-disabled="true"
        >
            Cancel Order
        </a>

    </div>

</div>
@endsection