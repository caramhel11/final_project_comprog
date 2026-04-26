<?php
// login.php — Customer login

require_once 'includes/functions.php';
startSession();

// Already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error   = '';
$success = flashGet('success');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $result = loginCustomer($email, $password);
        if ($result['success']) {
            flashSet('success', $result['message']);
            redirect('index.php');
        } else {
            $error = $result['message'];
        }
    }
}

$pageTitle = 'Sign In — Isla Finds';
$cssPath   = 'css/style.css';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="css/style.css">
</head>
<body style="background:linear-gradient(135deg,#EEF2FF 0%,#F9FAFB 100%);min-height:100vh;">

<div class="min-h-screen flex items-center justify-center px-4">
  <div class="auth-card w-full">

    <!-- Logo -->
    <div class="text-center mb-8">
      <a href="index.php" class="nav-logo text-2xl" style="text-decoration:none">
        Isla <span style="color:#D97706">Finds</span>
      </a>
      <h1 class="mt-4 mb-1">Welcome Back</h1>
      <p class="text-sm text-gray-500">Sign in to your account</p>
    </div>

    <?php if ($error): ?>
      <div class="flash flash-error"><?= sanitize($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="flash flash-success"><?= sanitize($success) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="mb-4">
        <label class="form-label" for="email">Email Address</label>
        <input
          type="email" id="email" name="email"
          class="form-input"
          placeholder="you@email.com"
          value="<?= sanitize($_POST['email'] ?? '') ?>"
          required autofocus
        >
      </div>

      <div class="mb-6">
        <label class="form-label" for="password">Password</label>
        <input
          type="password" id="password" name="password"
          class="form-input"
          placeholder="••••••••"
          required
        >
      </div>

      <button type="submit" class="form-btn">Sign In</button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-6">
      Don't have any account?
      <a href="register.php" class="font-semibold text-indigo-600 hover:text-indigo-800">Create one free</a>
    </p>

    <hr class="divider">

    <p class="text-center text-xs text-gray-400">
      <a href="admin/login.php" class="hover:text-indigo-500 transition">Admin login →</a>
    </p>
  </div>
</div>
</body>
</html>