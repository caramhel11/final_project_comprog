<?php
// register.php — Customer registration

require_once 'includes/functions.php';
startSession();

if (isLoggedIn()) {
    redirect('index.php');
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['fullname']  ?? '');
    $email    = trim($_POST['email']     ?? '');
    $password = trim($_POST['password']  ?? '');
    $confirm  = trim($_POST['confirm']   ?? '');

    if (empty($fullName) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $result = registerCustomer($fullName, $email, $password);
        if ($result['success']) {
            flashSet('success', 'Account created! Please sign in.');
            redirect('login.php');
        } else {
            $error = $result['message'];
        }
    }
}

$pageTitle = 'Create Account — Isla Finds';
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

<div class="min-h-screen flex items-center justify-center px-4 py-12">
  <div class="auth-card w-full">

    <div class="text-center mb-8">
      <a href="index.php" class="nav-logo text-2xl" style="text-decoration:none">
        Isla <span style="color:#D97706">Finds</span>
      </a>
      <h1 class="mt-4 mb-1">Create your account</h1>
      <p class="text-sm text-gray-500">Join us and start shopping island treasures</p>
    </div>

    <?php if ($error): ?>
      <div class="flash flash-error"><?= sanitize($error) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="mb-4">
        <label class="form-label" for="fullname">Full Name</label>
        <input
          type="text" id="fullname" name="fullname"
          class="form-input"
          placeholder="Your full name"
          value="<?= sanitize($_POST['fullname'] ?? '') ?>"
          required autofocus
        >
      </div>

      <div class="mb-4">
        <label class="form-label" for="email">Email Address</label>
        <input
          type="email" id="email" name="email"
          class="form-input"
          placeholder="you@email.com"
          value="<?= sanitize($_POST['email'] ?? '') ?>"
          required
        >
      </div>

      <div class="mb-4">
        <label class="form-label" for="password">Password</label>
        <input
          type="password" id="password" name="password"
          class="form-input"
          placeholder="At least 6 characters"
          required
        >
      </div>

      <div class="mb-6">
        <label class="form-label" for="confirm">Confirm Password</label>
        <input
          type="password" id="confirm" name="confirm"
          class="form-input"
          placeholder="Re-enter password"
          required
        >
      </div>

      <button type="submit" class="form-btn">Create Account</button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-6">
      Already have an account?
      <a href="login.php" class="font-semibold text-indigo-600 hover:text-indigo-800">Sign in</a>
    </p>
  </div>
</div>
</body>
</html>
