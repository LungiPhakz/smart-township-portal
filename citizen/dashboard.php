
<?php
session_start();
if (!isset($_SESSION['citizen_logged_in'])) {
    header("Location: login.php");
    exit;
}
$citizenName = $_SESSION['citizen_name'] ?? 'Citizen';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Citizen Dashboard | STMP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
body {
  margin: 0;
  font-family: "Poppins", sans-serif;
  color: #fff;
  background: url('assets/cover2.png') no-repeat center center fixed;
  background-size: cover;
}
.overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.6);
  z-index: 0;
}
.container {
  position: relative;
  z-index: 1;
}

/* top navigation */
.topbar {
  background: rgba(0,0,0,0.5);
  color: #fff;
  padding: 0.6rem 1rem;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  box-shadow: 0 3px 10px rgba(0,0,0,0.3);
  backdrop-filter: blur(10px);
}
.btn-profile {
  background: #ff6600;
  color: #fff;
  border-radius: 50%;
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  margin-right: 0.6rem;
}
.dropdown-menu {
  background: rgba(0,0,0,0.85);
  color: #fff;
}
.dropdown-menu a {
  color: #fff;
}
.dropdown-menu a:hover {
  background: #ff6600;
  color: #fff;
}

/* main content */
.main-content {
  min-height: calc(100vh - 60px);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}
.card-glass {
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(14px);
  border: 1px solid rgba(255,255,255,0.25);
  border-radius: 14px;
  padding: 2rem;
  width: 100%;
  max-width: 800px;
  text-align: center;
  box-shadow: 0 8px 25px rgba(0,0,0,0.4);
}
.card-glass h2 {
  color: #ff6600;
  font-weight: 700;
}
.card-glass p {
  color: rgba(255,255,255,0.9);
  font-size: 16px;
}
.action-buttons {
  margin-top: 2rem;
}
.action-buttons a {
  display: inline-block;
  background: #ff6600;
  color: #fff;
  padding: 10px 24px;
  border-radius: 6px;
  text-decoration: none;
  margin: 0 10px;
  transition: all 0.3s ease;
}
.action-buttons a:hover {
  background: #ff8533;
  transform: translateY(-3px);
}
</style>
</head>
<body>
<div class="overlay"></div>

<!-- === TOPBAR === -->
<div class="topbar">
  <div class="dropdown">
    <button class="btn btn-sm dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="background:none;border:none;color:white;">
      <div class="btn-profile"><?= strtoupper(substr($citizenName,0,1)) ?></div>
      <span><?= htmlspecialchars($citizenName) ?></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
      <li><a class="dropdown-item" href="report_issue.php"><i class="bi bi-pencil-square me-1"></i> Report Issue</a></li>
      <li><a class="dropdown-item" href="track_status.php"><i class="bi bi-eye me-1"></i> Track Requests</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-1"></i> Logout</a></li>
    </ul>
  </div>
</div>

<!-- === MAIN SECTION === -->
<div class="main-content">
  <div class="card-glass">
    <h2>Welcome, <?= htmlspecialchars($citizenName) ?></h2>
    <p>
      Manage your requests easily through our <strong>Smart Township Management Platform</strong>.
      You can report new issues or track the progress of your previous submissions.
    </p>
    <div class="action-buttons">
      <a href="report_issue.php"><i class="bi bi-pencil-square me-1"></i> Report an Issue</a>
      <a href="track_status.php"><i class="bi bi-activity me-1"></i> Track My Requests</a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
