<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Profile</title>
</head>
<body>
    <main>
        <h1>My Profile</h1>

        @if (session('success'))
            <div>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Profile Information --}}
        <section>
            <h2>Profile Information</h2>

            <form
                method="POST"
                action="{{ route('customer.profile.update') }}"
            >
                @csrf
                @method('PUT')

                <div>
                    <label for="name">Name</label>

                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name', auth()->user()->name) }}"
                        required
                    >

                    @error('name')
                        <div>{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="email">Email</label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email', auth()->user()->email) }}"
                        required
                    >

                    @error('email')
                        <div>{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit">
                    Save Changes
                </button>
            </form>
        </section>

        <hr>

        {{-- Change Password --}}
        <section>
            <h2>Change Password</h2>

            <form
                method="POST"
                action="{{ route('customer.profile.password.update') }}"
            >
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password">
                        Current Password
                    </label>

                    <input
                        id="current_password"
                        type="password"
                        name="current_password"
                        required
                    >

                    @error('current_password')
                        <div>{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="password">
                        New Password
                    </label>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                    >

                    @error('password')
                        <div>{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation">
                        Confirm New Password
                    </label>

                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                    >
                </div>

                <button type="submit">
                    Change Password
                </button>
            </form>
        </section>
    </main>
</body>
</html>