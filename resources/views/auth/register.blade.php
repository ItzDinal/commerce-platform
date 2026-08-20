<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
</head>
<body>

    <h1>Create Account</h1>

    @if ($errors->any())
        <div>
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register.store') }}">
        @csrf

        <div>
            <label for="name">Name</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
            >
        </div>

        <div>
            <label for="email">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
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
            <label for="password_confirmation">Confirm Password</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
            >
        </div>

        <button type="submit">Create Account</button>
    </form>

    <div>
        <a href="{{ route('google.redirect') }}">
            Continue with Google
        </a>
    </div>

    <p>
        Already have an account?
        <a href="{{ route('login') }}">Login</a>
    </p>

</body>
</html>