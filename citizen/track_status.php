<?php
session_start();
require_once __DIR__ . '/../config/db_config.php';

// Redirect if not logged in
if (!isset($_SESSION['citizen_logged_in'])) {
    header("Location: login.php");
    exit;
}

$citizen_id = $_SESSION['citizen_id'];

try {
    // ✅ Match your real columns
    $stmt = $pdo->prepare("
       SELECT sr.request_uuid, sr.location, sr.description AS category_description, sr.status, sr.created_at,
               c.name AS category_name
        FROM service_requests sr
        LEFT JOIN categories c ON sr.category_id = c.id
        WHERE sr.citizen_id = ?
        ORDER BY sr.created_at DESC
    ");
    $stmt->execute([$citizen_id]);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<p style='color:red; text-align:center;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>");
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Track Report Status - Smart Township Management Platform</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<style>
:root {
  --accent:#ff6600;
  --overlay: rgba(0,0,0,0.6);
}
body {
  margin: 0;
  padding: 0;
  font-family: "Poppins", sans-serif;
  color: #fff;
  background: url('assets/cover2.png') no-repeat center center fixed;
  background-size: cover;
}
.overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: var(--overlay);
  z-index: 0;
}
.container {
  position: relative;
  z-index: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  flex-direction: column;
}
.card {
  background: rgba(0,0,0,0.75);
  padding: 30px;
  width: 600px;
  border-radius: 8px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.5);
}
.card h2 {
  text-align:center;
  margin-bottom:20px;
  color:#fff;
}
.table {
  color: #fff;
  font-size: 14px;
}
.table th {
  color: var(--accent);
  border-bottom: 2px solid var(--accent);
}
.table td { border-bottom: 1px solid rgba(255,255,255,0.1); }
.card a {
  color: var(--accent);
  text-decoration: none;
}
.card a:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="overlay"></div>
<div class="top-nav">
    <a href="dashboard.php">back</a>
    
</div>
<div class="container">
  <div class="card">
    <h2>Your Submitted Reports</h2>
    <?php if (empty($reports)): ?>
      <p>No reports submitted yet.</p>
    <?php else: ?>
      <table class="table table-borderless">
        <thead>
          <tr>
            <th>Report ID</th>
            <th>Category</th>
            <th>Issue</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($reports as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['request_uuid']) ?></td>
              <td><?= htmlspecialchars($r['category_name'] ?? 'Unassigned') ?></td>
              <td><?= htmlspecialchars($r['category_description']) ?></td>
              <td>
                <?php
                  $status = $r['status'];
                  $badge = match($status) {
                    'NEW' => 'bg-warning text-dark',
                    'ASSIGNED' => 'bg-info text-dark',
                    'IN_PROGRESS' => 'bg-primary',
                    'ON_HOLD' => 'bg-secondary',
                    'RESOLVED' => 'bg-success',
                    'CLOSED' => 'bg-dark',
                    'REJECTED' => 'bg-danger',
                    default => 'bg-light text-dark'
                  };
                  echo "<span class='badge $badge'>" . htmlspecialchars($status) . "</span>";
                ?>
              </td>
              <td><?= date('Y-m-d', strtotime($r['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <p class="text-center mt-3">
      <a href="report_issue.php">Submit New Report</a> | 
      <a href="logout.php">Logout</a>
    </p>
  </div>
</div>
</body>
</html>
