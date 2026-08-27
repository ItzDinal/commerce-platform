<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
</head>
<body>

    <h1>Forgot Password</h1>

    <p>
        Enter your email address and we'll send you a password reset link.
    </p>

    @if (session('status'))
        <div>
            <p>{{ session('status') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div>
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
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

        <button type="submit">Send Password Reset Link</button>
    </form>

    <p>
        <a href="{{ route('login') }}">Back to Login</a>
    </p>

</body>
</html>