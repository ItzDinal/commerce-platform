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

        <dl>
            <dt>Name</dt>
            <dd>{{ auth()->user()->name }}</dd>

            <dt>Email</dt>
            <dd>{{ auth()->user()->email }}</dd>

            <dt>Email Verified</dt>
            <dd>
                {{ auth()->user()->email_verified_at ? 'Yes' : 'No' }}
            </dd>
        </dl>
    </main>
</body>
</html>