<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>
</head>
<body>
    <h1>My Account</h1>

    <p>Welcome, {{ auth()->user()->name }}</p>

    <nav>
        <a href="{{ route('customer.profile') }}">Profile</a>
        <a href="{{ route('customer.addresses.index') }}">Addresses</a>
    </nav>
</body>
</html>