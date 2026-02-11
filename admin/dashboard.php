<?php
session_start();
require_once __DIR__ . '/../config/db_config.php';

// LOGIN VALIDATION (ADMIN ONLY)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'ADMIN') {
    header('Location: login.php');
    exit;
}

// ---- Fetch summary statistics ----
$counts = [
    'total' => 0,
    'new' => 0,
    'in_progress' => 0,
    'resolved' => 0
];

try {
    $counts['total'] = $pdo->query("SELECT COUNT(*) FROM service_requests")->fetchColumn();
    $counts['new'] = $pdo->query("SELECT COUNT(*) FROM service_requests WHERE status='NEW'")->fetchColumn();
    $counts['in_progress'] = $pdo->query("SELECT COUNT(*) FROM service_requests WHERE status='IN_PROGRESS'")->fetchColumn();
    $counts['resolved'] = $pdo->query("SELECT COUNT(*) FROM service_requests WHERE status='RESOLVED'")->fetchColumn();
} catch(Exception $e) {}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin Dashboard - STMP</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* ===== GLOBAL LAYOUT FIX ===== */
html, body {
    height: 100%;
    overflow: hidden;
}

/* Sidebar */
.sidebar {
    width: 240px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    overflow: hidden;
}

/* Main Content */
.main {
    margin-left: 240px;
    height: 100vh;
    overflow-y: auto;
    padding: 24px;
}
.main h3 {
    color: #ff6600;
    margin-bottom: 1rem;
}


/* Glass card (same as summary) */
.glass-card {
    background: rgba(0,0,0,0.75);
    border-radius: 16px;
    padding: 18px;
    backdrop-filter: blur(6px);
    color: #fff;
}

/* Summary cards */
.summary-card {
    background: rgba(0,0,0,0.75);
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: .2s;
}
.summary-card:hover {
    transform: translateY(-4px);
}
/* Equal chart cards */
.chart-card {
    height: 360px;          /* SAME HEIGHT */
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* Force canvas to fit container */
.chart-card canvas {
    max-height: 260px !important;
}
</style>
</head>

<body>

<?php include_once __DIR__ . '/includes/sidebar.php'; ?>

<div class="main">

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-warning">Admin Dashboard</h3>
    <span>Welcome, <strong>Admin</strong></span>
  </div>

  <!-- Summary -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="summary-card">
        <h6>Total Requests</h6>
        <h2><?= $counts['total'] ?></h2>
      </div>
    </div>
    <div class="col-md-3">
      <div class="summary-card">
        <h6>Open</h6>
        <h2><?= $counts['new'] ?></h2>
      </div>
    </div>
    <div class="col-md-3">
      <div class="summary-card">
        <h6>In Progress</h6>
        <h2><?= $counts['in_progress'] ?></h2>
      </div>
    </div>
    <div class="col-md-3">
      <div class="summary-card">
        <h6>Resolved</h6>
        <h2><?= $counts['resolved'] ?></h2>
      </div>
    </div>
  </div>

  <!-- Map + Table -->
  <div class="row g-3 mb-4">
    <div class="col-lg-7">
      <div class="glass-card">
        <div id="map" style="height:280px;border-radius:12px;"></div>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="glass-card">
        <h6 class="fw-bold mb-3">Recent Requests</h6>
        <div id="recent-table" class="text-muted text-center">Loading…</div>
      </div>
    </div>
  </div>

  <!-- Charts -->
  <div class="row g-3 mb-5">
    <div class="col-lg-6">
      <div class="glass-card chart-card">
        <h6 class="fw-bold mb-3">Requests by Status</h6>
        <canvas id="barChart"></canvas>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="glass-card chart-card">
        <h6 class="fw-bold mb-3">Status Distribution</h6>
        <canvas id="pieChart"></canvas>
      </div>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
/* MAP */
const map = L.map('map').setView([-26.205, 28.047], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

/* CHARTS */
new Chart(barChart, {
  type:'bar',
  data:{
    labels:['New','In Progress','Resolved'],
    datasets:[{
      data:[<?= $counts['new']?>,<?= $counts['in_progress']?>,<?= $counts['resolved']?>],
      backgroundColor:['#ff3b30','#f0ad4e','#28a745']
    }]
  }
});

new Chart(pieChart, {
  type:'pie',
  data:{
    labels:['New','In Progress','Resolved'],
    datasets:[{
      data:[<?= $counts['new']?>,<?= $counts['in_progress']?>,<?= $counts['resolved']?>],
      backgroundColor:['#ff3b30','#f0ad4e','#28a745']
    }]
  }
});
</script>

</body>
</html>
