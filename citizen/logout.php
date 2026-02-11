<?php
session_start();

// Destroy all session data
$_SESSION = [];
session_unset();
session_destroy();

// Optional: display a brief logout message before redirect
header("Refresh: 60; URL=login.php"); // redirect after 2 seconds
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Logout - SmartTown </title>
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
    background: url('assets/cover2.png') no-repeat center center fixed !important;
    background-size: cover !important;
    height: 100vh;

    display: flex;
    justify-content: center;
    align-items: center;

  
}
.overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: var(--overlay);
  z-index: 0;
}
    .logout-card {
      width: 420px;
    padding: 35px;
    border-radius: 16px;

    background: rgba(0,0,0,0.75);
    border: 1px solid rgba(255,255,255,0.15);
    backdrop-filter: blur(6px);

    text-align: center;
    box-shadow: 0 8px 25px rgba(0,0,0,0.45);
    }

    .logout-card h3 {
    color: #ff6600;
    margin-bottom: 18px;
    font-weight: 600;
    }
    .logout-card p {
      color: #ddd;
    margin-bottom: 25px; 
    }
    .logout-card .btn-login {
      background: #ff6600;
    border: none;
    padding: 12px 20px;
    font-size: 16px;
    color: #fff;
    border-radius: 8px;
    width: 100%;
    cursor: pointer;
    transition: 0.3s ease;
    }
    .logout-card .btn-login:hover {
      background: #ff8533;
    }
  </style>
</head>
<body>
  <div class="overlay"></div>
  <div class="logout-card">
    <h3>Logged Out</h3>
    <p>You are now logged out. Thank you for using the STMP platform.</p>
    <a href="login.php" class="btn btn-login">Back to Login</a>
  </div>
  </div>
</body>
</html>
