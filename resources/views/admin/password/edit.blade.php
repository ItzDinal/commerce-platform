<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Password</title>
</head>
<body>

    <h1>Change Admin Password</h1>

    @if (session('success'))
        <div>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div>
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.password.update') }}">
        @csrf
        @method('PUT')

        <div>
            <label for="current_password">Current Password</label>

            <input
                id="current_password"
                type="password"
                name="current_password"
                required
            >
        </div>

        <div>
            <label for="password">New Password</label>

            <input
                id="password"
                type="password"
                name="password"
                required
            >
        </div>

        <div>
            <label for="password_confirmation">Confirm New Password</label>

            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
            >
        </div>

        <button type="submit">
            Update Password
        </button>
    </form>

</body>
</html>