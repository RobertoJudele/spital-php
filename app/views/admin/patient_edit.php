<?php
require_once 'app/helpers/esc.php';
require_once 'app/middleware/Csrf.php';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Edit patient</title>
  <link rel="stylesheet" href="<?= $base ?>/public/assets/css/dashboard-shared.css">
</head>
<body>
  <div class="page">
    <div class="container">
      <header class="appbar">
        <div class="brand"><h1>Edit patient</h1></div>
        <div class="actions">
          <a class="btn" href="<?= $base ?>/index.php?r=spital/patients/index">Back</a>
        </div>
      </header>

      <section class="card col-12">
        <?php if (!empty($_SESSION['error'])): ?>
          <div class="error"><?= e($_SESSION['error']) ?></div>
          <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['msg'])): ?>
          <div class="notice"><?= e($_SESSION['msg']) ?></div>
          <?php unset($_SESSION['msg']); ?>
        <?php endif; ?>

        <form action="<?= $base ?>/index.php?r=spital/admin/patients/update" method="POST">
          <input type="hidden" name="csrf_token" value="<?= e(
              Csrf::token(),
          ) ?>">
          <input type="hidden" name="id" value="<?= (int) ($patient[
              'user_id'
          ] ?? 0) ?>">

          <div class="grid grid-2">
            <label>Prenume
              <input type="text" name="first_name" value="<?= e(
                  $patient['first_name'] ?? '',
              ) ?>" required maxlength="128">
            </label>
            <label>Nume
              <input type="text" name="last_name" value="<?= e(
                  $patient['last_name'] ?? '',
              ) ?>" required maxlength="128">
            </label>
          </div>

          <div class="grid grid-2">
            <label>Email
              <input type="email" name="email" value="<?= e(
                  $patient['email'] ?? '',
              ) ?>" required maxlength="128">
            </label>
            <label>Parolă (lasă gol pentru a păstra)
              <input type="password" name="password" maxlength="128">
            </label>
          </div>

          <div class="grid grid-3">
            <label>CNP
              <input type="text" name="cnp" value="<?= e(
                  $patient['cnp'] ?? '',
              ) ?>" maxlength="13" pattern="\d{13}">
            </label>
            <label>Telefon
              <input type="text" name="phone" value="<?= e(
                  $patient['phone'] ?? '',
              ) ?>" maxlength="32">
            </label>
            <label>Grupa sanguină
              <input type="text" name="blood_type" value="<?= e(
                  $patient['blood_type'] ?? '',
              ) ?>" maxlength="8" placeholder="ex: A+, O-">
            </label>
          </div>

          <label>Adresă
            <input type="text" name="address" value="<?= e(
                $patient['address'] ?? '',
            ) ?>" maxlength="255">
          </label>

          <label>Alergii
            <textarea name="allergies" rows="3"><?= e(
                $patient['allergies'] ?? '',
            ) ?></textarea>
          </label>

          <div class="form-actions" style="margin-top:1rem;">
            <button type="submit" class="primary-btn">Save changes</button>
            <a class="btn" href="<?= $base ?>/index.php?r=spital/patients/index">Cancel</a>
          </div>
        </form>
      </section>
    </div>
  </div>
</body>
</html>