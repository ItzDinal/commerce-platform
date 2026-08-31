<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Admin Dashboard</h1>

    <h2>Current Admin</h2>

    <p>
        <strong>Name:</strong>
        {{ $admin->name }}
    </p>

    <p>
        <strong>Email:</strong>
        {{ $admin->email }}
    </p>

    <p>
        <strong>Role:</strong>
        {{ $admin->role }}
    </p>
</body>
</html>