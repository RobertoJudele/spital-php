<!-- filepath: /Applications/XAMPP/xamppfiles/htdocs/spital-php/app/views/patients/create.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Patient</title>
</head>
<body>
    <h1>Create a New Patient</h1>
    <?php $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); ?>
    <form action="<?= $base ?>/index.php?r=spital/patients/create" method="POST">
        <label for="first_name">First Name:</label>
        <input type="text" name="first_name" id="first_name" maxlength="128" required>
        <br>

        <label for="last_name">Last Name:</label>
        <input type="text" name="last_name" id="last_name" maxlength="128" required>
        <br>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" maxlength="128" required>
        <br>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" maxlength="128" required>
        <br>

        <label for="cnp">CNP:</label>
        <input type="text" name="cnp" id="cnp" maxlength="13" required>
        <br>

        <label for="phone">Phone:</label>
        <input type="text" name="phone" id="phone" maxlength="32">
        <br>

        <label for="address">Address:</label>
        <textarea name="address" id="address" rows="4" cols="50"></textarea>
        <br>

        <label for="blood_type">Blood Type:</label>
        <select name="blood_type" id="blood_type">
            <option value="A+">A+</option>
            <option value="A-">A-</option>
            <option value="B+">B+</option>
            <option value="B-">B-</option>
            <option value="AB+">AB+</option>
            <option value="AB-">AB-</option>
            <option value="O+">O+</option>
            <option value="O-">O-</option>
        </select>
        <br>

        <label for="allergies">Allergies:</label>
        <textarea name="allergies" id="allergies" rows="4" cols="50"></textarea>
        <br>

        <button type="submit">Create Patient</button>
    </form>
</body>
</html>