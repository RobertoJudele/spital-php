<?php
require_once 'app/middleware/Csrf.php';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Doctor</title>
</head>
<body>
  <h1>Edit Doctor #<?= (int) $doctor['id'] ?></h1>
  <form action="<?= $base ?>/index.php?r=spital/doctors/edit" method="POST">
    <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
    <input type="hidden" name="id" value="<?= (int) $doctor['id'] ?>">

    <label>First Name:
      <input type="text" name="first_name" value="<?= e(
          $doctor['first_name'],
      ) ?>" required maxlength="128">
    </label><br>

    <label>Last Name:
      <input type="text" name="last_name" value="<?= e(
          $doctor['last_name'],
      ) ?>" required maxlength="128">
    </label><br>

    <label>Email:
      <input type="email" name="email" value="<?= e(
          $doctor['email'],
      ) ?>" required maxlength="128">
    </label><br>

    <label>Password (leave blank to keep):
      <input type="password" name="password" maxlength="128">
    </label><br>

    <label>Department:
      <input type="text" name="department" value="<?= e(
          $doctor['department'] ?? '',
      ) ?>" required>
    </label><br>

    <label>Specialization:
      <input type="text" name="specialization" value="<?= e(
          $doctor['specialization'] ?? '',
      ) ?>" required>
    </label><br>

    <label>Grade:
      <input type="text" name="grade" value="<?= e(
          $doctor['grade'] ?? '',
      ) ?>" required>
    </label><br>

    <button type="submit">Save</button>
    <a href="<?= $base ?>/index.php?r=spital/admin/dashboard">Cancel</a>
  </form>
</body>
</html>