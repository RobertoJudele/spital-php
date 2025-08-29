
<?php
require_once 'app/helpers/esc.php';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Appointment #<?= (int) $appointment[
    'id'
] ?></title></head>
<body>
  <h1>Appointment #<?= (int) $appointment['id'] ?></h1>
  <ul>
    <li>Date: <?= e($appointment['date']) ?></li>
    <li>Status: <?= e((string) $appointment['status']) ?></li>
    <li>Doctor: <?= e(
        ($appointment['doctor_last'] ?? '') .
            ' ' .
            ($appointment['doctor_first'] ?? ''),
    ) ?> (<?= e($appointment['doctor_email'] ?? '') ?>)</li>
  </ul>
  <p><a href="<?= $base ?>/index.php?r=spital/patients/dashboard">Back to dashboard</a></p>
</body>
</html>