<?php
// admin/login.php — Admin authentication

require_once '../includes/functions.php';
startSession();

// Already logged in
if (isAdminLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = flashGet('success');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $result = loginAdmin($username, $password);
        if ($result['success']) {
            flashSet('success', 'Welcome to admin panel!');
            redirect('dashboard.php');
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — Isla Finds</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body style="background:linear-gradient(135deg,#EEF2FF 0%,#F9FAFB 100%);min-height:100vh;">

<div class="min-h-screen flex items-center justify-center px-4">
  <div class="auth-card w-full">

    <!-- Logo -->
    <div class="text-center mb-8">
      <a href="../index.php" class="nav-logo text-2xl" style="text-decoration:none;justify-content:center;display:flex;">
        Isla <span style="color:#D97706">Finds</span>
      </a>
      <h1 class="mt-4 mb-1">Admin Panel</h1>
      <p class="text-sm text-gray-500">Staff login required</p>
    </div>

    <?php if ($error): ?>
      <div class="flash flash-error"><?= sanitize($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="flash flash-success"><?= sanitize($success) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="mb-4">
        <label class="form-label" for="username">Username</label>
        <input
          type="text" id="username" name="username"
          class="form-input"
          placeholder="staff username"
          value="<?= sanitize($_POST['username'] ?? '') ?>"
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

    <hr class="divider">

    <p class="text-center text-xs text-gray-400">
      <a href="../index.php" class="hover:text-indigo-500 transition">Back to shop →</a>
    </p>

  </div>
</div>

</body>
</html>