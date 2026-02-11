<?php
session_start();
require_once __DIR__ . '/../config/db_config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {

            // 💾 SET SESSION
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = strtoupper($user['role']); // ADMIN or SUPERVISOR
            $_SESSION['department_id'] = $user['department_id'];

            // 🎯 REDIRECT BASED ON ROLE
            switch ($_SESSION['role']) {
                case 'ADMIN':
                    header("Location: dashboard.php");
                    exit;
                case 'SUPERVISOR':
                    header("Location: supervisor_dashboard.php");
                    exit;
                default:
                    $error = "Unknown role detected. Contact support.";
            }

        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Please fill in both fields";
    }
}
?>


<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Login - SmartTown</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
     :root{
  
  --overlay: rgba(0,0,0,0.6);
}
    body {
      
        margin: 0;
      padding: 0;
      font-family: "Poppins", sans-serif;
      color: #fff;
      background: url('assets/image/cover2.png') no-repeat center center fixed;
      background-size: cover;

      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
     backdrop-filter: blur(3px);
    
    }
    .overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: var(--overlay);
  z-index: 0;
}
    .login-card {
       width: 420px;
      padding: 2.5rem;
      border-radius: 16px;

      background: rgba(0,0,0,0.75);
      backdrop-filter: blur(7px);

      box-shadow: 0 8px 28px rgba(0,0,0,0.45);
      text-align: center;
    }
    .login-card h3 {
      color: #ff6600;
      font-weight: 600;
      margin-bottom: 1.5rem;
    }

    .form-label {
      color: #fff;
      font-size: 15px;
    }

    /* Inputs */
    .form-control {
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.2);
      color: #fff;
    }

    .form-control:focus {
      background: rgba(255,255,255,0.15);
      border-color: #ff6600;
      color: #fff;
    }
    .btn-login {
      background: #ff6600;
      color: #fff;
      padding: 10px;
      border-radius: 8px;
      border: none;
      font-size: 16px;
    }
    .btn-login:hover {
      background: #ff8533;
    }
    .alert-danger {
      background: rgba(255,0,0,0.35);
      color: white;
      border: 1px solid rgba(255,0,0,0.5);
    }
    /* === Top Navigation (Right corner menu) === */
        .top-nav {
            position: absolute;
            top: 20px;
            right: 30px;
            display: flex;
            gap: 25px;
            font-size: 16px;
            font-weight: 500;
            z-index: 5;
        }

        .top-nav a {
            color: #fff;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 8px;
            transition: 0.3s;
        }

        .top-nav a:hover {
            background: rgba(255, 255, 255, 0.2);
        }
  </style>
</head>
<body>
<div class="overlay"></div>
<!-- Top Navigation -->
<div class="top-nav">
    <a href="../index.html">Home</a>
    
</div>
<div class="login-card">
  <h3><i class="bi bi-shield-lock"></i> Admin Login</h3>

  <?php if($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post" action="">
    <div class="mb-3">
      <label for="username" class="form-label">Username</label>
      <input type="text" class="form-control" id="username" name="username" required autofocus>
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <div class="d-grid">
      <button type="submit" class="btn btn-login">Login</button>
    </div>
  </form>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
