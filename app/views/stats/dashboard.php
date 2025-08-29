<?php
require_once 'app/helpers/esc.php';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<!DOCTYPE html>
<html lang="en" data-base="<?= e($base) ?>">
<head>
  <meta charset="UTF-8">
  <title>Hospital Statistics</title>
  <link rel="stylesheet" href="<?= $base ?>/public/assets/css/stats.css">
</head>
<body>
  <div class="container">
    <header>
      <a href="<?= $base ?>/index.php?r=spital/admin/dashboard" class="back-btn">← Back to Admin</a>
      <h1>Hospital Statistics Dashboard</h1>
    </header>

    <div class="stats-grid">
      <div class="chart-container">
        <h3>Appointments by Status</h3>
        <div class="chart-wrapper"><canvas id="appointmentsChart"></canvas></div>
      </div>
      <div class="chart-container">
        <h3>Patients by Blood Type</h3>
        <div class="chart-wrapper"><canvas id="bloodTypeChart"></canvas></div>
      </div>
      <div class="chart-container">
        <h3>Doctors by Department</h3>
        <div class="chart-wrapper"><canvas id="departmentChart"></canvas></div>
      </div>
      <div class="chart-container">
        <h3>Room Occupancy</h3>
        <div class="chart-wrapper"><canvas id="occupancyChart"></canvas></div>
      </div>
    </div>
  </div>

  <script src="<?= $base ?>/public/assets/js/chart.umd.min.js"></script>
  <script src="<?= $base ?>/public/assets/js/stats.js"></script>
</body>
</html>
