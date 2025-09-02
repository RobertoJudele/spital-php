<!-- filepath: /Applications/XAMPP/xamppfiles/htdocs/spital-php/app/views/doctors/create.php -->
<?php
require_once 'app/helpers/esc.php';
require_once 'app/middleware/Csrf.php';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Creează cont doctor</title>
  <link rel="stylesheet" href="<?= $base ?>/public/assets/css/dashboard-shared.css">
  <link rel="stylesheet" href="<?= $base ?>/public/assets/css/login.css">
  <script>
    const specializations = {
      "Cardiologie": ["Cardiologie intervențională", "Electrofiziologie", "Cardiologie pediatrică"],
      "Neurologie": ["Neurochirurgie", "Neurologie pediatrică", "Epileptologie"],
      "Chirurgie": ["Chirurgie generală", "Chirurgie plastică", "Chirurgie vasculară"],
      "Pediatrie": ["Neonatologie", "Pediatrie generală", "Pediatrie oncologică"],
      "Radiologie": ["Radiologie imagistică", "Radioterapie", "Radiologie intervențională"],
      "Oncologie": ["Oncologie medicală", "Hematologie oncologică", "Oncologie pediatrică"]
    };

    function updateSpecializations() {
      const department = document.getElementById("department").value;
      const specializationSelect = document.getElementById("specialization");
      specializationSelect.innerHTML = '<option value="" disabled selected>Alege specializarea</option>';
      if (specializations[department]) {
        specializations[department].forEach(specialization => {
          const option = document.createElement("option");
          option.value = specialization;
          option.textContent = specialization;
          specializationSelect.appendChild(option);
        });
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      updateSpecializations();
    });
  </script>
</head>
<body>
  <div class="login-page">
    <section class="card auth-card">
      <h1>Creează cont doctor</h1>

      <?php if (!empty($_SESSION['error'])): ?>
        <div class="error"><?= e($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>
      <?php if (!empty($_SESSION['msg'])): ?>
        <div class="notice"><?= e($_SESSION['msg']) ?></div>
        <?php unset($_SESSION['msg']); ?>
      <?php endif; ?>

      <!-- scoate 'novalidate' ca să funcționeze validarea din browser -->
      <form action="<?= $base ?>/index.php?r=spital/doctors/create" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">

        <label>Prenume
          <input type="text" name="first_name" id="first_name" maxlength="128" required>
        </label>

        <label>Nume
          <input type="text" name="last_name" id="last_name" maxlength="128" required>
        </label>

        <label>Email
          <input type="email" name="email" id="email" maxlength="128" required>
        </label>

        <label>Parolă
          <input type="password" name="password" id="password" maxlength="128" required>
        </label>

        <label>Departament
          <select name="department" id="department" required onchange="updateSpecializations()">
            <option value="" disabled selected>Alege departamentul</option>
            <option value="Cardiologie">Cardiologie</option>
            <option value="Neurologie">Neurologie</option>
            <option value="Chirurgie">Chirurgie</option>
            <option value="Pediatrie">Pediatrie</option>
            <option value="Radiologie">Radiologie</option>
            <option value="Oncologie">Oncologie</option>
          </select>
        </label>

        <label>Specializare
          <select name="specialization" id="specialization" required>
            <option value="" disabled selected>Alege specializarea</option>
          </select>
        </label>

        <label>Grad
          <select name="grade" id="grade" required>
            <option value="" disabled selected>Alege gradul</option>
            <option value="Rezident">Rezident</option>
            <option value="Specialist">Specialist</option>
            <option value="Primar">Primar</option>
            <option value="Asistent universitar">Asistent universitar</option>
            <option value="Șef de secție">Șef de secție</option>
            <option value="Director medical">Director medical</option>
          </select>
        </label>

        <button type="submit">Creează doctor</button>
      </form>
    </section>
  </div>
</body>
</html>