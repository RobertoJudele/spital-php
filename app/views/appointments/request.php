<?php
require_once 'app/middleware/Csrf.php';
require_once 'app/helpers/esc.php';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Request appointment</title>
</head>
<body>
  <h1>Request appointment</h1>
  <?php if (!empty($_SESSION['error'])): ?>
    <p style="color:red"><?= e($_SESSION['error']) ?></p>
  <?php endif; ?>
  <form action="<?= $base ?>/index.php?r=spital/appointments/request" method="POST">
    <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
    <label>Doctor:
      <select name="doctor_id" required>
        <option value="">Select a doctor</option>
        <?php foreach ($doctors as $d): ?>
          <option value="<?= (int) $d['doctor_id'] ?>">
            <?= e(
                $d['last_name'] .
                    ' ' .
                    $d['first_name'] .
                    ' (' .
                    $d['email'] .
                    ')',
            ) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label><br><br>
    <label>Date and time:
      <input type="datetime-local" name="date" required>
    </label><br><br>
    <button type="submit">Send request</button>
    <a href="<?= $base ?>/index.php?r=spital/patients/dashboard">Cancel</a>
  </form>
</body>
</html>