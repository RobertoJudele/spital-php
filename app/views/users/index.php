<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
</head>
<body>
    <h1>All users</h1>
    <table>
        <tr><th>First name</th><th>Last name</th><th>Email</th></tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= e($user['first_name']) ?></td>
                <td><?= e($user['last_name']) ?></td>
                <td><?= e($user['email']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>