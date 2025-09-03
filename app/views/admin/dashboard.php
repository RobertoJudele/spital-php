<?php
// presupune că e() este încărcată (app/helpers/esc.php) în front controller
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); ?>
<?php require_once 'app/middleware/Csrf.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="<?= $base ?>/public/assets/css/dashboard-shared.css">
</head>
<body>
  <header class="appbar">
    <div class="brand">
      <div class="logo"></div>
      <h1>Admin Dashboard</h1>
    </div>
    <div class="actions">
      <form action="<?= $base ?>/index.php?r=spital/auth/logout" method="POST" style="margin-bottom:0">
        <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
        <button type="submit">Logout</button>
      </form>
      <a class="btn" href="<?= $base ?>/index.php?r=spital/stats/dashboard" style="margin-left:8px;">📊 Statistics</a>
      <a class="btn" href="<?= $base ?>/index.php?r=spital/patients/index">All patients</a>
    </div>
  </header>

  <?php if (!empty($_SESSION['msg'])): ?>
    <div class="notice"><?= e($_SESSION['msg']) ?></div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['error'])): ?>
    <div class="error"><?= e($_SESSION['error']) ?></div>
  <?php endif; ?>

  <div class="row">
    <section class="card col-12">
      <h2>Doctors</h2>
      <table>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Department</th>
          <th>Specialization</th>
          <th>Grade</th>
        </tr>
        <?php foreach ($doctors as $i => $d): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= e(
                ($d['last_name'] ?? '') . ' ' . ($d['first_name'] ?? ''),
            ) ?></td>
            <td><?= e($d['email'] ?? '') ?></td>
            <td><?= e($d['department'] ?? '') ?></td>
            <td><?= e($d['specialization'] ?? '') ?></td>
            <td><?= e($d['grade'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    </section>

    <section class="card col-12">
      <h2>Rooms</h2>
      <?php if (!empty($_SESSION['msg'])): ?>
        <p style="color:green"><?= e($_SESSION['msg']) ?></p>
      <?php endif; ?>
      <?php if (!empty($_SESSION['error'])): ?>
        <p style="color:red"><?= e($_SESSION['error']) ?></p>
      <?php endif; ?>

      <div style="display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap;">
        <div style="flex:2 1 480px; min-width:360px;">
          <?php if (!empty($rooms)): ?>
            <table>
              <tr>
                <th>#</th><th>Room</th><th>Capacity</th><th>Department</th><th>Description</th>
              </tr>
              <?php foreach ($rooms as $r): ?>
                <tr>
                  <td><?= (int) $r['id'] ?></td>
                  <td><?= e($r['room_number']) ?></td>
                  <td><?= (int) $r['capacity'] ?></td>
                  <td><?= e($r['department']) ?></td>
                  <td><?= e($r['description'] ?? '') ?></td>
                </tr>
              <?php endforeach; ?>
            </table>
          <?php else: ?>
            <p>No rooms yet.</p>
          <?php endif; ?>
        </div>

        <div style="flex:1 1 320px; min-width:300px; border:1px solid #ddd; padding:12px; border-radius:8px;">
          <h3>Create room</h3>
          <form action="<?= $base ?>/index.php?r=spital/admin/create-room" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e(
                Csrf::token(),
            ) ?>">
            <label>Department
              <select name="department_id" required>
                <option value="">Select department</option>
                <?php foreach ($departments as $d): ?>
                  <option value="<?= (int) $d['id'] ?>"><?= e(
    $d['name'],
) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <br>
            <label>Room number
              <input type="text" name="room_number" required>
            </label>
            <br>
            <label>Capacity
              <input type="number" name="capacity" min="1" value="1" required>
            </label>
            <br>
            <label>Description
              <input type="text" name="description" placeholder="optional">
            </label>
            <br>
            <button type="submit">Create room</button>
          </form>
        </div>
      </div>
    </section>

    <section class="card col-6">
      <h2>Data import</h2>
      <form action="<?= $base ?>/index.php?r=spital/admin/import-medications" method="POST" style="margin-bottom:12px">
        <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
        <button type="submit">Import medications (FDA)</button>
      </form>
    </section>

    <section class="card col-12">
      <h2>Pending appointments</h2>
      <?php if (!empty($pendingAppointments)): ?>
        <table>
          <tr><th>#</th><th>Date</th><th>Patient</th><th>Doctor</th><th>Actions</th></tr>
          <?php foreach ($pendingAppointments as $a): ?>
            <tr>
              <td><?= (int) $a['id'] ?></td>
              <td><?= e($a['date']) ?></td>
              <td><?= e($a['patient_last'] . ' ' . $a['patient_first']) ?></td>
              <td><?= e($a['doctor_last'] . ' ' . $a['doctor_first']) ?></td>
              <td class="actions" style="display:flex; gap:.5rem">
                <form action="<?= $base ?>/index.php?r=spital/appointments/approve" method="POST">
                  <input type="hidden" name="csrf_token" value="<?= e(
                      Csrf::token(),
                  ) ?>">
                  <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                  <button type="submit">Approve</button>
                </form>
                <form action="<?= $base ?>/index.php?r=spital/appointments/reject" method="POST">
                  <input type="hidden" name="csrf_token" value="<?= e(
                      Csrf::token(),
                  ) ?>">
                  <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                  <button type="submit">Reject</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php else: ?>
        <p class="muted">No pending requests.</p>
      <?php endif; ?>
    </section>
  </div>
</body>
</html>
