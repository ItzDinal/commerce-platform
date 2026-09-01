@extends('layouts.customer')

@section('title', 'My Orders')

@section('content')
<div class="container mx-auto px-4 py-8">

    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-gray-900">
            My Orders
        </h1>

        <p class="mt-1 text-sm text-gray-600">
            View your orders and track their current status.
        </p>
    </div>

    {{-- Orders --}}
    @if ($orders->isNotEmpty())

        <div class="space-y-6">

            @foreach ($orders as $order)

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

                    // Defensive fallback in case an unexpected status
                    // somehow exists in the database.
                    if ($currentIndex === false) {
                        $currentIndex = 0;
                    }

                    $itemCount = $order->items->sum('quantity');

                    $shippingMethod = match ($order->shipping_method) {
                        'standard' => 'Standard Shipping',
                        default => ucfirst(
                            str_replace('_', ' ', $order->shipping_method)
                        ),
                    };
                @endphp

                <article class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">

                    {{-- Order Header --}}
                    <div class="border-b border-gray-200 p-5">

                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Order Number
                                </p>

                                <p class="mt-1 font-semibold text-gray-900">
                                    {{ $order->order_number }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Order Date
                                </p>

                                <p class="mt-1 text-sm text-gray-900">
                                    {{ $order->created_at->format('d M Y, h:i A') }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                    Status
                                </p>

                                <p class="mt-1 font-semibold text-gray-900">
                                    {{ $order->status->label() }}
                                </p>
                            </div>

                        </div>

                    </div>

                    {{-- Order Summary --}}
                    <div class="p-5">

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">

                            {{-- Items --}}
                            <div>
                                <p class="text-sm text-gray-500">
                                    Items
                                </p>

                                <p class="mt-1 text-sm font-medium text-gray-900">
                                    {{ $itemCount }}
                                    {{ $itemCount === 1 ? 'item' : 'items' }}
                                </p>
                            </div>

                            {{-- Shipping --}}
                            <div>
                                <p class="text-sm text-gray-500">
                                    Shipping
                                </p>

                                <p class="mt-1 text-sm font-medium text-gray-900">
                                    {{ $shippingMethod }}
                                </p>
                            </div>

                            {{-- Total --}}
                            <div>
                                <p class="text-sm text-gray-500">
                                    Total
                                </p>

                                <p class="mt-1 text-sm font-semibold text-gray-900">
                                    LKR {{ number_format($order->total) }}
                                </p>
                            </div>

                        </div>

                        {{-- Status Progress --}}
                        <div class="mt-8">

                            <p class="mb-4 text-sm font-semibold text-gray-900">
                                Order Status
                            </p>

                            <div class="overflow-x-auto pb-2">

                                <div class="flex min-w-max items-start">

                                    @foreach ($statuses as $index => $status)

                                        @php
                                            $isCompleted = $index <= $currentIndex;
                                            $isCurrent = $index === $currentIndex;
                                        @endphp

                                        <div class="flex items-start">

                                            {{-- Status Step --}}
                                            <div class="flex w-24 flex-col items-center text-center">

                                                {{-- Circle --}}
                                                <div
                                                    class="flex h-9 w-9 items-center justify-center rounded-full border-2
                                                    {{ $isCompleted
                                                        ? 'border-gray-900 bg-gray-900 text-white'
                                                        : 'border-gray-300 bg-white text-gray-400' }}"
                                                >

                                                    @if ($isCompleted)

                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            viewBox="0 0 20 20"
                                                            fill="currentColor"
                                                            class="h-4 w-4"
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

                                                {{-- Status Label --}}
                                                <span
                                                    class="mt-2 text-xs leading-4
                                                    {{ $isCurrent
                                                        ? 'font-semibold text-gray-900'
                                                        : 'text-gray-500' }}"
                                                >
                                                    {{ $status->label() }}
                                                </span>

                                            </div>

                                            {{-- Connector --}}
                                            @if (!$loop->last)

                                                <div
                                                    class="mt-4 h-0.5 w-8
                                                    {{ $index < $currentIndex
                                                        ? 'bg-gray-900'
                                                        : 'bg-gray-300' }}"
                                                ></div>

                                            @endif

                                        </div>

                                    @endforeach

                                </div>

                            </div>

                        </div>

                        {{-- Actions --}}
                        <div class="mt-8 flex flex-col gap-3 border-t border-gray-200 pt-5 sm:flex-row">

                            {{-- View Order --}}
                            <a
                                href="{{ route('customer.orders.show', $order) }}"
                                class="inline-flex items-center justify-center rounded-md bg-gray-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-gray-800"
                            >
                                View Order
                            </a>

                            {{-- Customer cancellation is intentionally
                                 not implemented at this stage.
                                 This will eventually point to the
                                 business WhatsApp number. --}}
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

                </article>

            @endforeach

        </div>

        {{-- Pagination --}}
        @if ($orders->hasPages())
            <div class="mt-8">
                {{ $orders->links() }}
            </div>
        @endif

    @else

        {{-- Empty State --}}
        <div class="rounded-lg border border-gray-200 bg-white px-6 py-12 text-center">

            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    class="h-6 w-6 text-gray-500"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M9 3.75h6m-7.5 0A2.25 2.25 0 005.25 6v14.25h13.5V6A2.25 2.25 0 0015.75 3.75M9 3.75v2.25h6V3.75M8.25 10.5h7.5M8.25 14h5.25"
                    />
                </svg>

            </div>

            <h2 class="mt-4 text-lg font-semibold text-gray-900">
                No orders yet
            </h2>

            <p class="mt-2 text-sm text-gray-600">
                You haven't placed any orders yet.
            </p>

            <a
                href="{{ route('home') }}"
                class="mt-6 inline-flex items-center justify-center rounded-md bg-gray-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-gray-800"
            >
                Continue Shopping
            </a>

        </div>

    @endif

</div>
@endsection