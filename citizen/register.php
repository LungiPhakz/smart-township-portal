<?php
session_start();
require_once __DIR__ . '/../config/db_config.php';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$name || !$email || !$phone || !$password) {
        $errors[] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } elseif ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM citizens WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors[] = "Email already registered.";
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO citizens (full_name, email, phone, password, created_at) VALUES (?,?,?,?,NOW())");
        $stmt->execute([$name, $email, $phone, $hash]);
        $_SESSION['citizen_logged_in'] = true;
        $_SESSION['citizen_name'] = $name;
        $_SESSION['citizen_id'] = $pdo->lastInsertId();
        header("Location: dashboard.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Register | STMP</title>
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
    <h1>Join <span>Smart Township</span> Platform</h1>
    <p>Create an account to start submitting service requests and track updates from your local municipality in real time. 
       Be part of building a smarter, more connected community.</p>
    <a href="login.php" class="btn">Login</a>
  </div>

  <div class="card">
    <h2>Register Here</h2>

    <?php if ($errors): ?>
      <div style="color:#ff6666;font-size:14px;text-align:center;">
        <?php foreach ($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?>
      </div>
    <?php endif; ?>

    <form method="post">
      <input type="text" name="name" placeholder="Enter Full Name" required>
      <input type="email" name="email" placeholder="Enter Email" required>
      <input type="text" name="phone" placeholder="Enter Phone Number" required>
      <input type="password" name="password" placeholder="Enter Password" required>
      <input type="password" name="confirm_password" placeholder="Confirm Password" required>
      <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login here</a></p>
  </div>
</div>

</body>
</html>
