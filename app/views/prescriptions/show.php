
<?php
require_once 'app/helpers/esc.php';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Prescription #<?= (int) $prescription[
    'id'
] ?></title></head>
<body>
<h1>Prescription #<?= (int) $prescription['id'] ?></h1>
<p>Doctor: <?= e(
    ($prescription['doctor_last'] ?? '') .
        ' ' .
        ($prescription['doctor_first'] ?? ''),
) ?></p>
<pre><?= e($prescription['prescription'] ?? '') ?></pre>
<p><a href="<?= $base ?>/index.php?r=spital/patients/dashboard">Back</a></p>
</body></html>