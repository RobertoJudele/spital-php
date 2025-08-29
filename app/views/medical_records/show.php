
<?php
require_once 'app/helpers/esc.php';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Medical record #<?= (int) $record[
    'id'
] ?></title></head>
<body>
<h1>Medical record #<?= (int) $record['id'] ?></h1>
<p>Doctor: <?= e(
    ($record['doctor_last'] ?? '') . ' ' . ($record['doctor_first'] ?? ''),
) ?></p>
<p>Initial observations:</p>
<pre><?= e($record['initial_observations'] ?? '') ?></pre>
<p><a href="<?= $base ?>/index.php?r=spital/patients/dashboard">Back</a></p>
</body></html>