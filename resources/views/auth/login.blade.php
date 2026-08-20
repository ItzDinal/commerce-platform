<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>

    <h1>Login</h1>

    @if ($errors->any())
        <div>
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}">
        @csrf

        <div>
            <label for="email">Email</label>
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

        <button type="submit">Login</button>
    </form>

    <div>
        <a href="{{ route('google.redirect') }}">
            Continue with Google
        </a>
    </div>

    <p>
        Don't have an account?
        <a href="{{ route('register') }}">Create an account</a>
    </p>

    <p>
        <a href="{{ route('password.request') }}">Forgot your password?</a>
    </p>

</body>
</html>