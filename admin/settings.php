<?php
session_start();
require_once __DIR__ . '/../config/db_config.php';

// Redirect if not logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Optional: Fetch admin details (assuming admins table has username, email)
$admin_username = $_SESSION['admin_username'] ?? 'Admin';
$admin_email = '';

$stmt = $pdo->prepare("SELECT email FROM admins WHERE username = :username");
$stmt->execute(['username' => $admin_username]);
$row = $stmt->fetch();
if ($row) {
    $admin_email = $row['email'] ?? '';
}

// Handle POST for updating password (example)
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Fetch current password hash
    $stmt = $pdo->prepare("SELECT password_hash FROM admins WHERE username = :username");
    $stmt->execute(['username' => $admin_username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($current_password, $admin['password_hash'])) {
        if ($new_password === $confirm_password) {
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE admins SET password_hash = :hash WHERE username = :username");
            $update->execute(['hash' => $new_hash, 'username' => $admin_username]);
            $success_msg = "Password updated successfully!";
        } else {
            $error_msg = "New passwords do not match.";
        }
    } else {
        $error_msg = "Current password is incorrect.";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Settings - SmartTown Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    * ===== GLOBAL LAYOUT FIX ===== */
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

   .settings-card {
   
    
    background: rgba(0, 0, 0, 0.29);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    backdrop-filter: blur(6px);
    color: #fff;
}
    /* Card headings */
.settings-card h4 {
    color: #ff6600;
    margin-bottom: 1.5rem;
    font-weight: 600;
}
    .btn-save {
    background-color: #ff6600;
    color: #fff;
    border: none;
    padding: 0.6rem 1.4rem;
    border-radius: 8px;
}
    .btn-save:hover {
      background-color: #e94218;
    }

    /* Form input styling to match theme */
.form-control, .form-select {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.18);
    color: #fff;
}
.form-control:focus, .form-select:focus {
    background: rgba(255,255,255,0.12);
    color: #0a0707ff;
    border-color: #ff6600;
    box-shadow: 0 0 0 0.25rem rgba(255,102,0,0.25);
}

    .form-icon {
      position: absolute;
      top: 50%;
      left: 12px;
      transform: translateY(-50%);
      color:  #ff6600;
    }
    .input-with-icon {
      position: relative;
    }
    .input-with-icon input {
      padding-left: 2.5rem;
    }
    /* Alerts matching theme */
.alert-success {
    background: rgba(0,255,100,0.15);
    color: #8affb7;
    border: 1px solid rgba(0,255,100,0.25);
}

.alert-danger {
    background: rgba(255,80,80,0.15);
    color: #ffb3b3;
    border: 1px solid rgba(255,80,80,0.25);
}
  </style>
</head>
<body>
 
  <!-- Sidebar -->
  <?php include_once 'includes/sidebar.php'; ?>

  <!-- Main content -->
  <div class="main  py-4">
    <div class="page-header mb-4">
      <h3 class="fw-bold text-white">Settings</h3>
    </div>

    <?php if($success_msg): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>
    <?php if($error_msg): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <!-- Profile Section -->
    <div class="settings-card">
      <h4><i class="bi bi-person-circle"></i> Profile Information</h4>
      <form>
        <div class="mb-3 input-with-icon">
          <i class="bi bi-person form-icon"></i>
          <input type="text" class="form-control" value="<?= htmlspecialchars($admin_username) ?>" readonly>
        </div>
        <div class="mb-3 input-with-icon">
          <i class="bi bi-envelope form-icon"></i>
          <input type="email" class="form-control" value="<?= htmlspecialchars($admin_email) ?>" readonly>
        </div>
      </form>
    </div>

    <!-- Change Password Section -->
    <div class="settings-card">
      <h4><i class="bi bi-key"></i> Change Password</h4>
      <form method="post" action="">
        <div class="mb-3 input-with-icon">
          <i class="bi bi-lock form-icon"></i>
          <input type="password" name="current_password" class="form-control" placeholder="Current Password" required>
        </div>
        <div class="mb-3 input-with-icon">
          <i class="bi bi-lock-fill form-icon"></i>
          <input type="password" name="new_password" class="form-control" placeholder="New Password" required>
        </div>
        <div class="mb-3 input-with-icon">
          <i class="bi bi-lock-fill form-icon"></i>
          <input type="password" name="confirm_password" class="form-control" placeholder="Confirm New Password" required>
        </div>
        <button type="submit" class="btn btn-save">Save Changes</button>
      </form>
    </div>

    <!-- System Settings Section (example placeholders) -->
    <div class="settings-card">
      <h4><i class="bi bi-gear"></i> System Settings</h4>
      <form>
        <div class="mb-3">
          <label for="siteName" class="form-label">Site Name</label>
          <input type="text" class="form-control" id="siteName" value="SmartTown Admin Panel">
        </div>
        <div class="mb-3">
          <label for="timezone" class="form-label">Timezone</label>
          <select class="form-select" id="timezone">
            <option value="Africa/Johannesburg" selected>Africa/Johannesburg</option>
            <option value="UTC">UTC</option>
          </select>
        </div>
        <button type="submit" class="btn btn-save">Save System Settings</button>
      </form>
    </div>
  </div>
   

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
