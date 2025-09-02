<?php
require_once 'app/helpers/esc.php';
require_once 'app/middleware/Csrf.php';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Creează cont pacient</title>
  <link rel="stylesheet" href="<?= $base ?>/public/assets/css/dashboard-shared.css">
  <link rel="stylesheet" href="<?= $base ?>/public/assets/css/login.css">
</head>
<body>
  <div class="login-page">
    <section class="card auth-card">
      <h1>Creează cont pacient</h1>

      <?php if (!empty($_SESSION['error'])): ?>
        <div class="error"><?= e($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>
      <?php if (!empty($_SESSION['msg'])): ?>
        <div class="notice"><?= e($_SESSION['msg']) ?></div>
        <?php unset($_SESSION['msg']); ?>
      <?php endif; ?>

      <form action="<?= $base ?>/index.php?r=spital/patients/create" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">

        <label for="first_name">Prenume
          <input id="first_name" type="text" name="first_name" maxlength="128" required>
        </label>

        <label for="last_name">Nume
          <input id="last_name" type="text" name="last_name" maxlength="128" required>
        </label>

        <label for="email">Email
          <input id="email" type="email" name="email" maxlength="128" required>
        </label>

        <label for="password">Parolă
          <input id="password" type="password" name="password" maxlength="128" required>
        </label>

        <label for="cnp">CNP
          <input id="cnp" type="text" name="cnp" maxlength="13" pattern="\d{13}" required>
        </label>

        <label for="phone">Telefon
          <input id="phone" type="text" name="phone" maxlength="32">
        </label>

        <label for="address">Adresă
          <textarea id="address" name="address" rows="3"></textarea>
        </label>

        <label for="blood_type">Grupa sanguină
          <select id="blood_type" name="blood_type" required>
            <option value="" disabled selected>Selectează</option>
            <option value="A+">A+</option><option value="A-">A-</option>
            <option value="B+">B+</option><option value="B-">B-</option>
            <option value="AB+">AB+</option><option value="AB-">AB-</option>
            <option value="O+">O+</option><option value="O-">O-</option>
          </select>
        </label>

        <label for="allergies">Alergii
          <textarea id="allergies" name="allergies" rows="3"></textarea>
        </label>

        <button type="submit">Creează cont</button>
      </form>

      <div style="margin-top: 1rem;">
        <a href="<?= $base ?>/index.php?r=spital/auth/login">Ai deja cont? Autentifică-te</a>
      </div>
    </section>
  </div>
</body>
</html>