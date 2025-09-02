<?php
require_once 'app/helpers/esc.php';
require_once 'app/middleware/Csrf.php';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Doctor Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="<?= $base ?>/public/assets/css/dashboard-shared.css">
</head>
<body>
  <div class="page">
    <div class="container">
      <header class="appbar">
        <div class="brand">
          <div class="logo"></div>
          <h1>Doctor Dashboard</h1>
        </div>
        <div class="actions">
          <form action="<?= $base ?>/index.php?r=spital/auth/logout" method="POST" style="margin:0">
            <input type="hidden" name="csrf_token" value="<?= e(
                Csrf::token(),
            ) ?>">
            <button type="submit">Logout</button>
          </form>
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
        <section class="card col-6">
          <h2>Create medical record</h2>
          <form action="<?= $base ?>/index.php?r=spital/doctor/create-medrec" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e(
                Csrf::token(),
            ) ?>">
            <label>Patient
              <select name="patient_id" required>
                <option value="">Select patient</option>
                <?php foreach ($patients as $p): ?>
                  <option value="<?= (int) $p['id'] ?>"><?= e(
    $p['last_name'] . ' ' . $p['first_name'] . ' (' . $p['email'] . ')',
) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <label>Initial observations
              <textarea name="initial_observations" rows="4" placeholder="Notes..."></textarea>
            </label>
            <button type="submit" class="primary-btn">Create</button>
          </form>
        </section>

        <section class="card col-6">
          <h2>Add consultation</h2>
          <form action="<?= $base ?>/index.php?r=spital/doctor/create-consult" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e(
                Csrf::token(),
            ) ?>">
            <label>Patient
              <select name="patient_id" required>
                <option value="">Select patient</option>
                <?php foreach ($patients as $p): ?>
                  <option value="<?= (int) $p['id'] ?>"><?= e(
    $p['last_name'] . ' ' . $p['first_name'],
) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <label>Medical record ID (optional; leave empty to auto-create)
              <input type="text" name="medical_record_id" placeholder="e.g. 42">
            </label>
            <label>Date and time
              <input type="datetime-local" name="consultation_date" required>
            </label>
            <label>Diagnosis
              <input type="text" name="diagnosis" placeholder="Diagnosis">
            </label>
            <label>Notes
              <textarea name="notes" rows="4" placeholder="Notes..."></textarea>
            </label>
            <button type="submit" class="primary-btn">Save consultation</button>
          </form>
        </section>

        <section class="card col-6">
          <h2>Create prescription</h2>
          <form action="<?= $base ?>/index.php?r=spital/doctor/create-prescription" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e(
                Csrf::token(),
            ) ?>">
            <label>Patient
              <select name="patient_id" required>
                <option value="">Select patient</option>
                <?php foreach ($patients as $p): ?>
                  <option value="<?= (int) $p['id'] ?>"><?= e(
    $p['last_name'] . ' ' . $p['first_name'] . ' (' . $p['email'] . ')',
) ?></option>
                <?php endforeach; ?>
              </select>
            </label>

            <label>Medication (from API imports)
              <select name="medication_ids[]" class="select-meds" multiple size="8">
                <option value="" disabled>Select medication(s)</option>
                <?php foreach ($medications ?? [] as $m): ?>
                  <option value="<?= (int) $m['id'] ?>"><?= e(
    $m['name'],
) ?></option>
                <?php endforeach; ?>
              </select>
              <div class="muted">Tip: Ține apăsat Cmd (Mac) sau Ctrl (Windows) pentru a selecta multiple.</div>
            </label>

            <label>Dosage
              <input type="text" name="dosage" placeholder="e.g. 500 mg, 1 tablet x 2/day">
            </label>

            <label>Notes / Instructions
              <textarea name="prescription" rows="4" placeholder="Additional instructions..."></textarea>
            </label>

            <button type="submit" class="primary-btn">Create prescription</button>
          </form>
          <?php if (empty($medications ?? [])): ?>
            <p class="muted">No medications imported yet. Use Admin → “Import medications (FDA)”.</p>
          <?php endif; ?>
        </section>

        <section class="card col-6">
          <h2>Create admission</h2>
          <form action="<?= $base ?>/index.php?r=spital/doctor/create-admission" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e(
                Csrf::token(),
            ) ?>">
            <label>Patient
              <select name="patient_id" required>
                <option value="">Select patient</option>
                <?php foreach ($patients as $p): ?>
                  <option value="<?= (int) $p['id'] ?>"><?= e(
    $p['last_name'] . ' ' . $p['first_name'],
) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <label>Room
              <select name="room_id" required>
                <option value="">Select room</option>
                <?php foreach ($rooms as $r): ?>
                  <option value="<?= (int) $r['id'] ?>"><?= e(
    ($r['room_number'] ?? 'Room ' . $r['id']) . ' (cap ' . $r['capacity'] . ')',
) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <label>Admission date/time
              <input type="datetime-local" name="admission_date" required>
            </label>
            <label>Discharge date/time (optional)
              <input type="datetime-local" name="discharge_date">
            </label>
            <button type="submit" class="primary-btn">Save admission</button>
          </form>
          <?php if (empty($rooms)): ?>
            <p class="muted">No rooms found. Add rooms in admin first.</p>
          <?php endif; ?>
        </section>

        <section class="card col-12">
          <h2>Recent prescriptions</h2>
          <table>
            <thead><tr><th>#</th><th>Patient</th></tr></thead>
            <tbody>
              <?php foreach ($recentPresc as $i => $p): ?>
                <tr>
                  <td>#<?= $i + 1 ?></td>
                  <td><?= e(
                      ($p['patient_last'] ?? '') .
                          ' ' .
                          ($p['patient_first'] ?? ''),
                  ) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </section>

        <section class="card col-12">
          <h2>Recent activity</h2>
          <div class="grid">
            <div class="col-3">
              <h3>Medical records</h3>
              <ul class="list">
                <?php foreach ($recentRecords as $i => $r): ?>
                  <li class="item">
                    <h4>#<?= $i + 1 ?></h4>
                    <div class="kv"><div><?= e(
                        $r['last_name'] . ' ' . $r['first_name'],
                    ) ?></div></div>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
            <div class="col-3">
              <h3>Consultations</h3>
              <ul class="list">
                <?php foreach ($recentConsults as $i => $c): ?>
                  <li class="item">
                    <h4>#<?= $i + 1 ?></h4>
                    <div class="kv">
                      <div><?= e($c['consultation_date']) ?></div>
                      <div>MR #<?= (int) $c['medical_record_id'] ?></div>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
            <div class="col-3">
              <h3>Prescriptions</h3>
              <ul class="list">
                <?php foreach ($recentPresc as $i => $p): ?>
                  <li class="item">
                    <h4>#<?= $i + 1 ?></h4>
                    <div class="kv">
                      <div><?= e(
                          ($p['patient_last'] ?? '') .
                              ' ' .
                              ($p['patient_first'] ?? ''),
                      ) ?></div>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
            <div class="col-3">
              <h3>Admissions</h3>
              <ul class="list">
                <?php foreach ($recentAdmissions as $a): ?>
                  <li class="item">
                    <h4>#<?= (int) $a['id'] ?></h4>
                    <div class="kv">
                      <div>patient #<?= (int) $a['patient_id'] ?></div>
                      <div><?= e($a['admission_date']) ?></div>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </section>
      </div>

    </div>
  </div>
</body>
</html>