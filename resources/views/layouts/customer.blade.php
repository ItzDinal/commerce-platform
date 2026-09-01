<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'My Account')
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900">

    <header class="border-b border-gray-200 bg-white">
        <div class="container mx-auto flex items-center justify-between px-4 py-4">
            <a
                href="{{ route('customer.dashboard') }}"
                class="font-semibold text-gray-900"
            >
                My Account
            </a>

            <nav class="flex items-center gap-4 text-sm">
                <a
                    href="{{ route('customer.dashboard') }}"
                    class="text-gray-600 hover:text-gray-900"
                >
                    Dashboard
                </a>

                <a
                    href="{{ route('customer.orders.index') }}"
                    class="text-gray-600 hover:text-gray-900"
                >
                    My Orders
                </a>

                <a
                    href="{{ route('customer.profile') }}"
                    class="text-gray-600 hover:text-gray-900"
                >
                    Profile
                </a>

                <a
                    href="{{ route('customer.addresses.index') }}"
                    class="text-gray-600 hover:text-gray-900"
                >
                    Addresses
                </a>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

</body>
</html>