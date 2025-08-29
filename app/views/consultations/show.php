
<?php
require_once 'app/helpers/esc.php';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Consultation #<?= (int) $consultation[
    'id'
] ?></title></head>
<body>
<h1>Consultation #<?= (int) $consultation['id'] ?></h1>
<p>Date: <?= e($consultation['consultation_date'] ?? '') ?></p>
<p>Doctor: <?= e(
    ($consultation['doctor_last'] ?? '') .
        ' ' .
        ($consultation['doctor_first'] ?? ''),
) ?></p>
<p>Medical record: #<?= (int) ($consultation['medical_record_id'] ?? 0) ?></p>
<p>Diagnosis: <?= e($consultation['diagnosis'] ?? '') ?></p>
<p>Notes:</p>
<pre><?= e($consultation['notes'] ?? '') ?></pre>
<p><a href="<?= $base ?>/index.php?r=spital/patients/dashboard">Back</a></p>
</body></html>