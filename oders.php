<?php
// orders.php — Customer order history

require_once 'includes/functions.php';
startSession();
requireLogin();

$pageTitle = 'My Orders — Isla Finds';
$customer  = currentCustomer();
$orders    = getOrdersByCustomer($customer['id']);
$flash     = flashGet('success');

include 'includes/header.php';
?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-12">
  <div class="mb-8 fade-up">
    <span class="section-tag">Account</span>
    <h1 class="section-title">My Orders</h1>
    <p class="text-gray-500 text-sm mt-1">Hello, <?= sanitize($customer['name']) ?>!</p>
  </div>

  <?php if ($flash): ?>
    <div class="flash flash-success fade-up"><?= sanitize($flash) ?></div>
  <?php endif; ?>

  <?php if (empty($orders)): ?>
    <div class="text-center py-24 fade-up bg-white rounded-2xl border border-gray-100">
      <div class="text-6xl mb-4">📦</div>
      <h2 class="text-lg font-semibold text-gray-600 mb-2">No orders yet</h2>
      <p class="text-gray-400 text-sm mb-6">Start shopping and your orders will appear here.</p>
      <a href="shop.php" class="btn-indigo">Shop Now</a>
    </div>
  <?php else: ?>
    <div class="space-y-4">
      <?php foreach ($orders as $i => $order): ?>
      <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm fade-up" style="animation-delay:<?= $i * .07 ?>s">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
          <div>
            <div class="flex items-center gap-3">
              <span class="font-bold text-indigo-700" style="font-family:'Playfair Display',serif">
                Order #<?= $order['OrderID'] ?>
              </span>
              <span class="status-badge status-<?= $order['Status'] ?>">
                <?= $order['Status'] ?>
              </span>
            </div>
            <div class="text-xs text-gray-400 mt-1">
              <?= date('F j, Y · g:i A', strtotime($order['OrderDate'])) ?>
            </div>
          </div>
          <div class="text-right">
            <div class="font-bold text-lg text-gray-800"><?= formatPrice((float)$order['TotalAmount']) ?></div>
            <div class="text-xs text-gray-400"><?= $order['item_count'] ?> item(s)</div>
          </div>
        </div>
        <hr class="border-gray-50 mb-3">
        <div class="flex items-center justify-between text-xs text-gray-400">
          <span>Thank you for shopping with Isla Finds! 🌺</span>
          <?php if ($order['Status'] === 'Pending'): ?>
            <span class="text-amber-500 font-medium">Processing your order...</span>
          <?php elseif ($order['Status'] === 'Delivered'): ?>
            <span class="text-green-500 font-medium">✓ Delivered</span>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
