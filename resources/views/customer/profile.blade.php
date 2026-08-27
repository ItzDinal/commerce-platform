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
            </div>

            <button type="submit">
                Save Changes
            </button>
        </form>
    </main>
</body>
</html>