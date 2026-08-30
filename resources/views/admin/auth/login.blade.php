<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login</title>
</head>
<body>

    <h1>Admin Portal</h1>

    <h2>Administrator Login</h2>

    @if (session('error'))
        <div>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div>
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login.submit') }}">
        @csrf

        <div>
            <label for="email">Admin Email</label>

            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
            >
        </div>

        <div>
            <label for="password">Password</label>

            <input
                id="password"
                type="password"
                name="password"
                required
            >
        </div>

        <div>
            <label>
                <input type="checkbox" name="remember" value="1">
                Remember me
            </label>
        </div>

        <button type="submit">
            Login to Admin Portal
        </button>
    </form>

</body>
</html>