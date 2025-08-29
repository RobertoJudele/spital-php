<?php
require_once 'config/recaptcha.php';
require_once 'app/helpers/esc.php';
require_once 'app/middleware/Csrf.php';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="<?= $base ?>/public/assets/css/dashboard-shared.css">
  <link rel="stylesheet" href="<?= $base ?>/public/assets/css/login.css">
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
  <div class="login-page">
    <section class="card auth-card">
      <h1>Login</h1>
      <?php if (!empty($_SESSION['error'])): ?>
        <div class="error"><?= e($_SESSION['error']) ?></div>
      <?php endif; ?>
      <?php if (!empty($_SESSION['msg'])): ?>
        <div class="notice"><?= e($_SESSION['msg']) ?></div>
      <?php endif; ?>

      <form action="<?= $base ?>/index.php?r=spital/auth/login" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
        <label>Email
          <input type="email" name="email" required>
        </label>
        <label>Password
          <input type="password" name="password" required>
        </label>

        <div class="g-recaptcha" data-sitekey="<?= RECAPTCHA_SITE_KEY ?>"></div>
        <br>

        <button type="submit">Sign in</button>
      </form>
    </section>
  </div>
</body>
</html>