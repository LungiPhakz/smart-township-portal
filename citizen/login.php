<?php
session_start();
require_once __DIR__ . '/../config/db_config.php';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $errors[] = "All fields are required.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM citizens WHERE email = ?");
        $stmt->execute([$email]);
        $citizen = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($citizen && password_verify($password, $citizen['password'])) {
            $_SESSION['citizen_logged_in'] = true;
            $_SESSION['citizen_id'] = $citizen['id'];
            $_SESSION['citizen_name'] = $citizen['full_name'];
            header("Location: dashboard.php");
            exit;
        } else {
            $errors[] = "Invalid email or password.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Citizen Login | STMP</title>
<link rel="stylesheet" href="assets/style.css">

</head>
<body>
<div class="overlay"></div>
<!-- Top Navigation -->
<div class="top-nav">
    <a href="../index.html">Home</a>
    
</div>
<div class="container">
  <div class="left-panel">
    <h1>Smart Township <span>Management Platform</span></h1>
    <p>The STMP enables citizens to report issues, request services, and track responses easily. 
       Log in to access your personalized dashboard and help improve your community.</p>
    <a href="register.php" class="btn">Join Us</a>
  </div>

  <div class="card">
    <h2>Login Here</h2>

    <?php if ($errors): ?>
      <div style="color:#ff6666;font-size:14px;text-align:center;">
        <?php foreach ($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?>
      </div>
    <?php endif; ?>

    <form method="post">
      <input type="email" name="email" placeholder="Enter Email Here" required>
      <input type="password" name="password" placeholder="Enter Password Here" required>
      <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Sign up here</a></p>
  </div>
</div>

</body>
</html>
