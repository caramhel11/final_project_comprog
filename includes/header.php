<?php
// includes/header.php — Shared site header
require_once __DIR__ . '/functions.php';
startSession();
$cartCount = cartCount();
$loggedIn  = isLoggedIn();
$customer  = currentCustomer();
$rootPath  = $rootPath ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?? 'Isla Finds — Marinduque Souvenir Shop' ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="<?= $cssPath ?? ($rootPath . 'css/style.css') ?>">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌺</text></svg>">
</head>
<body>

<!-- ── Navbar ── -->
<nav class="navbar" id="navbar">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">

    <a href="<?= $rootPath ?? '' ?>index.php" class="nav-logo">
      Isla <span>Finds</span>
    </a>

    <!-- Desktop nav -->
    <div class="hidden md:flex items-center gap-6">
      <a href="<?= $rootPath ?? '' ?>index.php" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition">Home</a>
      <a href="<?= $rootPath ?? '' ?>shop.php" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition">Shop</a>
      <a href="<?= $rootPath ?? '' ?>about.php" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition">About</a>
    </div>

    <div class="flex items-center gap-4">
      <!-- Cart -->
      <a href="<?= $rootPath ?? '' ?>cart.php" class="cart-icon-wrap" title="Cart">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <?php if ($cartCount > 0): ?>
          <span class="cart-badge"><?= $cartCount ?></span>
        <?php endif; ?>
      </a>

      <?php if ($loggedIn): ?>
        <div class="relative group">
          <button class="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-indigo-600 transition">
            <span class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold text-xs">
              <?= strtoupper(mb_substr($customer['name'], 0, 1)) ?>
            </span>
            <span class="hidden sm:inline"><?= sanitize(explode(' ', $customer['name'])[0]) ?></span>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div class="absolute right-0 mt-2 w-44 bg-white border border-gray-100 rounded-xl shadow-lg py-1 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
            <a href="<?= $rootPath ?? '' ?>orders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">My Orders</a>
            <hr class="my-1 border-gray-100">
            <a href="<?= $rootPath ?? '' ?>logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Logout</a>
          </div>
        </div>
      <?php else: ?>
        <a href="<?= $rootPath ?? '' ?>login.php" class="btn-indigo text-sm py-2 px-4">Sign In</a>
      <?php endif; ?>
    </div>

  </div>
</nav>

<script>
  // Navbar scroll effect
  window.addEventListener('scroll', () => {
    document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 20);
  });
</script>