<?php
require_once 'app/helpers/esc.php';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Patient Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    :root {
      --bg: #0b132b;
      --panel: #111a33;
      --panel2:#162246;
      --muted: #9aa3b2;
      --text: #e7eef7;
      --primary: #3a86ff;
      --accent: #00d1b2;
      --warn: #ffbe0b;
      --danger: #ef476f;
      --ok: #06d6a0;
      --border: #263258;
      --badge: #253259;
      --tableStripe: #1a2242;
      --glow: rgba(58,134,255,.35);
    }
    * { box-sizing: border-box; }
    html, body { height: 100%; }
    body {
      margin: 0;
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
      background:
        radial-gradient(1000px 600px at 20% -10%, rgba(58,134,255,.15), transparent 60%),
        radial-gradient(800px 500px at 110% 10%, rgba(0,209,178,.12), transparent 60%),
        linear-gradient(180deg, #0b132b 0%, #0f1a3a 100%);
      color: var(--text);
    }
    /* Centrare pe ecran */
    .page {
      min-height: 100dvh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 32px 16px;
    }
    .container {
      width: 100%;
      max-width: 1000px; /* mai îngust și centrat */
    }
    header.appbar {
      display: flex; align-items: center; justify-content: space-between;
      background: linear-gradient(180deg, rgba(28,37,65,.75), rgba(22,34,70,.75));
      backdrop-filter: blur(8px);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 16px 18px;
      margin-bottom: 22px;
      box-shadow: 0 10px 30px rgba(0,0,0,.25), 0 0 0 1px rgba(255,255,255,.02) inset;
    }
    .brand { display: flex; align-items: center; gap: 12px; }
    .brand .logo {
      width: 38px; height: 38px; border-radius: 12px;
      background: linear-gradient(135deg, var(--primary), #7bdeff);
      box-shadow: 0 8px 22px var(--glow);
    }
    .brand h1 { font-size: 18px; margin: 0; letter-spacing: .3px; }
    .actions a, .actions button {
      display: inline-flex; align-items: center; gap: 8px;
      border: 1px solid var(--border); color: var(--text);
      background: #172038; padding: 9px 13px; border-radius: 10px;
      text-decoration: none; cursor: pointer; transition: .2s ease;
    }
    .actions a:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(0,0,0,.25); }
    .primary-btn {
      background: linear-gradient(135deg, var(--primary), #6fb1ff);
      color: #0b132b; border: 0; border-radius: 10px; padding: 9px 13px;
      font-weight: 600; text-decoration: none;
      box-shadow: 0 10px 24px var(--glow);
    }
    .grid {
      display: grid;
      grid-template-columns: repeat(12, 1fr);
      gap: 18px;
    }
    .col-12 { grid-column: span 12; }
    .col-8  { grid-column: span 8; }
    .col-6  { grid-column: span 6; }
    .col-4  { grid-column: span 4; }
    @media (max-width: 1024px) { .col-8, .col-6, .col-4 { grid-column: span 12; } }

    .card {
      background: linear-gradient(180deg, var(--panel) 0%, var(--panel2) 100%);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 16px;
      box-shadow: 0 12px 34px rgba(0,0,0,0.28), 0 0 0 1px rgba(255,255,255,.02) inset;
    }
    .card h2 {
      margin: 0 0 12px 0; font-size: 18px; font-weight: 600;
      display: flex; align-items: center; justify-content: space-between;
    }
    .muted { color: var(--muted); }
    .toolbar { display: flex; gap: 10px; align-items: center; }

    table {
      width: 100%; border-collapse: collapse; margin-top: 6px;
      border-radius: 12px; overflow: hidden; border: 1px solid var(--border);
    }
    th, td {
      padding: 10px 12px; text-align: left; font-size: 14px;
      border-bottom: 1px solid var(--border);
    }
    thead th {
      background: #131b33; color: #c9d4e5; font-weight: 600;
    }
    tbody tr:nth-child(odd) { background: #141d37; }
    tbody tr:nth-child(even){ background: var(--tableStripe); }
    tbody tr:hover { background: rgba(255,255,255,.03); }

    .badge {
      display: inline-block; padding: 4px 8px; border-radius: 999px;
      font-size: 12px; border: 1px solid var(--border); background: var(--badge);
    }
    .badge.ok   { background: rgba(6,214,160,.12); color: #7ff3d2; border-color: rgba(6,214,160,.3); }
    .badge.warn { background: rgba(255,190,11,.12); color: #ffe08b; border-color: rgba(255,190,11,.3); }
    .badge.err  { background: rgba(239,71,111,.12); color: #ffc1cf; border-color: rgba(239,71,111,.35); }

    .list { display: grid; gap: 10px; margin-top: 8px; }
    .item {
      display: flex; flex-direction: column; gap: 6px;
      background: #172038; border: 1px solid var(--border);
      border-radius: 12px; padding: 12px;
    }
    .item h4 { margin: 0; font-size: 15px; }
    .kv { display: flex; gap: 12px; flex-wrap: wrap; }
    .kv div { color: var(--muted); font-size: 13px; }

    .flash { margin-bottom: 16px; }
    .flash p { margin: 0; padding: 10px 12px; border-radius: 10px; }
    .flash .ok  { background: rgba(6,214,160,.12); border: 1px solid rgba(6,214,160,.3); }
    .flash .err { background: rgba(239,71,111,.12); border: 1px solid rgba(239,71,111,.35); }
  </style>
</head>
<body>
  <div class="page">
    <div class="container">
      <header class="appbar">
        <div class="brand">
          <div class="logo"></div>
          <h1>Patient Dashboard</h1>
        </div>
        <div class="actions">
          <a class="primary-btn" href="<?= $base ?>/index.php?r=spital/appointments/request">Request appointment</a>
          <a href="<?= $base ?>/index.php?r=spital/auth/logout">Logout</a>
        </div>
      </header>

      <div class="flash">
        <?php if (!empty($_SESSION['msg'])): ?>
          <p class="ok"><?= e($_SESSION['msg']) ?></p>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
          <p class="err"><?= e($_SESSION['error']) ?></p>
        <?php endif; ?>
      </div>

      <div class="grid">
        <section class="card col-8">
          <h2>
            Appointments
            <span class="toolbar">
              <a class="primary-btn" href="<?= $base ?>/index.php?r=spital/appointments/request">New</a>
            </span>
          </h2>
          <?php if (!empty($appointments)): ?>
            <table>
              <thead>
                <tr><th>#</th><th>Date</th><th>Status</th><th>Doctor</th></tr>
              </thead>
              <tbody>
                <?php foreach ($appointments as $i => $a): ?>
                  <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($a['date']) ?></td>
                    <td>
                      <?php
                      $st = $a['status'];
                      $label =
                          $st === null
                              ? 'pending'
                              : ((int) $st === 1
                                  ? 'approved'
                                  : 'rejected');
                      $cls =
                          $st === null
                              ? 'warn'
                              : ((int) $st === 1
                                  ? 'ok'
                                  : 'err');
                      ?>
                      <span class="badge <?= $cls ?>"><?= e($label) ?></span>
                    </td>
                    <td><?= e(
                        ($a['doctor_last'] ?? '') .
                            ' ' .
                            ($a['doctor_first'] ?? ''),
                    ) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <p class="muted">No appointments yet.</p>
          <?php endif; ?>
        </section>

        <section class="card col-4">
          <h2>Last admission</h2>
          <?php if (!empty($lastAdmission)): ?>
            <div class="list">
              <div class="item">
                <h4>Admission #<?= (int) $lastAdmission['id'] ?></h4>
                <div class="kv">
                  <div>Department: <strong><?= e(
                      $lastAdmission['department'] ?? '—',
                  ) ?></strong></div>
                  <div>Room: <strong><?= e(
                      $lastAdmission['room_number'] ?? '—',
                  ) ?></strong> (cap <?= e(
    $lastAdmission['capacity'] ?? '—',
) ?>)</div>
                </div>
                <div class="kv">
                  <div>Admitted: <strong><?= e(
                      $lastAdmission['admission_date'] ?? '—',
                  ) ?></strong></div>
                  <div>Discharged: <strong><?= e(
                      $lastAdmission['discharge_date'] ?? '—',
                  ) ?></strong></div>
                </div>
              </div>
            </div>
          <?php else: ?>
            <p class="muted">No admissions found.</p>
          <?php endif; ?>
        </section>

        <section class="card col-6">
          <h2>Medical records</h2>
          <?php if (!empty($medicalRecords)): ?>
            <table>
              <thead>
                <tr><th>#</th><th>Doctor</th><th>Initial observations</th></tr>
              </thead>
              <tbody>
                <?php foreach ($medicalRecords as $i => $mr): ?>
                  <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e(
                        ($mr['doctor_last'] ?? '') .
                            ' ' .
                            ($mr['doctor_first'] ?? ''),
                    ) ?></td>
                    <td><?= e($mr['initial_observations'] ?? '') ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <p class="muted">No medical records.</p>
          <?php endif; ?>
        </section>

        <section class="card col-6">
          <h2>Consultations</h2>
          <?php if (!empty($consultations)): ?>
            <table>
              <thead>
                <tr><th>#</th><th>Date</th><th>Doctor</th><th>Diagnosis</th><th>Notes</th></tr>
              </thead>
              <tbody>
                <?php foreach ($consultations as $i => $c): ?>
                  <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($c['consultation_date']) ?></td>
                    <td><?= e(
                        ($c['doctor_last'] ?? '') .
                            ' ' .
                            ($c['doctor_first'] ?? ''),
                    ) ?></td>
                    <td><?= e($c['diagnosis'] ?? '') ?></td>
                    <td><?= e($c['notes'] ?? '') ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <p class="muted">No consultations.</p>
          <?php endif; ?>
        </section>

        <section class="card col-12">
          <h2>Prescriptions</h2>
          <?php if (!empty($prescriptions)): ?>
            <table>
              <thead>
                <tr><th>#</th><th>Doctor</th><th>Prescription</th></tr>
              </thead>
              <tbody>
                <?php foreach ($prescriptions as $i => $p): ?>
                  <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e(
                        ($p['doctor_last'] ?? '') .
                            ' ' .
                            ($p['doctor_first'] ?? ''),
                    ) ?></td>
                    <td>
                      <div style="white-space: pre-line;"><?= e(
                          $p['prescription'] ?? '',
                      ) ?></div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <p class="muted">No prescriptions.</p>
          <?php endif; ?>
        </section>
      </div>
    </div>
  </div>
</body>
</html>