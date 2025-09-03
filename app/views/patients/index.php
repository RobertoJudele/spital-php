<?php
require_once 'app/helpers/esc.php';
require_once 'app/middleware/Csrf.php'; // adăugat
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patients</title>
</head>
<body>
    <h1>All patients</h1>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Email</th>
          <th>CNP</th>
          <th>Phone</th>
          <th>Blood Type</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($patients as $i => $p): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= e(
                ($p['last_name'] ?? '') . ' ' . ($p['first_name'] ?? ''),
            ) ?></td>
            <td><?= e($p['email'] ?? '') ?></td>
            <td><?= e($p['cnp'] ?? '') ?></td>
            <td><?= e($p['phone'] ?? '') ?></td>
            <td><?= e($p['blood_type'] ?? '') ?></td>
            <td class="actions" style="display:flex; gap:.5rem; align-items:center;">
              <a class="btn" href="<?= $base ?>/index.php?r=spital/admin/patients/edit&id=<?= (int) ($p[
    'user_id'
] ?? 0) ?>">Edit</a>
              <form action="<?= $base ?>/index.php?r=spital/admin/patients/delete" method="POST" style="margin:0"
                    onsubmit="return confirm('Sigur dorești să ștergi acest pacient?');">
                <input type="hidden" name="csrf_token" value="<?= e(
                    Csrf::token(),
                ) ?>">
                <input type="hidden" name="id" value="<?= (int) ($p[
                    'user_id'
                ] ?? 0) ?>">
                <button type="submit" class="btn danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
</body>
</html>