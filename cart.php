<?php
// cart.php — Shopping cart and checkout

require_once 'includes/functions.php';
startSession();

$pageTitle = 'Cart — Isla Finds';

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $pid = (int)($_POST['product_id'] ?? 0);
            if ($pid > 0) { 
                addToCart($pid); 
                flashSet('success', 'Added to cart!');
            }
            break;

        case 'update':
            $pid = (int)($_POST['product_id'] ?? 0);
            $qty = (int)($_POST['qty'] ?? 0);
            if ($pid > 0) updateCartQty($pid, $qty);
            break;

        case 'remove':
            $pid = (int)($_POST['product_id'] ?? 0);
            if ($pid > 0) removeFromCart($pid);
            break;

        case 'checkout':
            requireLogin();
            $customer = currentCustomer();
            $total = cartTotal();
            $result = placeOrder($customer['id'], $total);
            if ($result['success']) {
                flashSet('success', "Order #{$result['order_id']} placed successfully! 🎉");
                redirect('orders.php');
            } else {
                flashSet('error', $result['message']);
            }
            break;
    }
    redirect('cart.php');
}

$cartItems = getCartItems();
$total = cartTotal();
$flash = flashGet('success') ?? flashGet('error');

include 'includes/header.php';
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">

  <div class="mb-8 fade-up">
    <span class="section-tag">Your Selection</span>
    <h1 class="section-title">Shopping Cart</h1>
  </div>

  <?php if ($flash): ?>
    <div class="flash flash-<?= str_contains($flash, '!') ? 'success' : 'error' ?>"><?= sanitize($flash) ?></div>
  <?php endif; ?>

  <?php if (empty($cartItems)): ?>
    <div class="text-center py-28 fade-up">
      <div class="text-7xl mb-5">🛒</div>
      <h2 class="text-xl font-semibold text-gray-700 mb-2">Your cart is empty</h2>
      <p class="text-gray-500 mb-6 text-sm">Discover our handcrafted Marinduque treasures.</p>
      <a href="shop.php" class="btn-indigo">Browse Products →</a>
    </div>

  <?php else: ?>
    <div class="grid lg:grid-cols-3 gap-8">

      <!-- Cart Items -->
      <div class="lg:col-span-2 fade-up">
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
          <table class="cart-table">
            <thead>
              <tr>
                <th style="padding:1.1rem 1.5rem">Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cartItems as $item): ?>
              <tr>
                <td style="padding:1rem 1.5rem">
                  <div class="flex items-center gap-3">
                    <img src="<?= productImage($item) ?>"
                         alt="<?= sanitize($item['ProductName']) ?>"
                         class="w-14 h-14 rounded-xl object-cover">
                    <div>
                      <div class="font-semibold text-sm text-gray-800"><?= sanitize($item['ProductName']) ?></div>
                      <div class="text-xs text-gray-400"><?= sanitize($item['CategoryName']) ?></div>
                    </div>
                  </div>
                </td>

                <td>
                  <span class="font-medium text-indigo-700"><?= formatPrice((float)$item['Price']) ?></span>
                </td>

                <td>
                  <form method="POST" style="display:flex;gap:0.5rem;align-items:center;">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="product_id" value="<?= $item['ProductID'] ?>">
                    <button type="submit" name="qty" value="<?= max(1, $item['cart_qty'] - 1) ?>" 
                            style="width:28px;height:28px;border:1px solid #E5E7EB;border-radius:0.375rem;cursor:pointer;font-weight:600;">−</button>
                    <span class="text-sm font-semibold w-6 text-center"><?= $item['cart_qty'] ?></span>
                    <button type="submit" name="qty" value="<?= $item['cart_qty'] + 1 ?>" 
                            style="width:28px;height:28px;border:1px solid #E5E7EB;border-radius:0.375rem;cursor:pointer;font-weight:600;">+</button>
                  </form>
                </td>

                <td>
                  <span class="font-bold text-gray-800"><?= formatPrice($item['subtotal']) ?></span>
                </td>

                <td>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="product_id" value="<?= $item['ProductID'] ?>">
                    <button type="submit" style="background:none;border:none;color:#F87171;font-size:1.5rem;cursor:pointer;font-weight:bold;" title="Remove">×</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="mt-4">
          <a href="shop.php" style="color:#4F46E5;text-decoration:none;font-size:0.875rem;font-weight:600;">
            ← Continue Shopping
          </a>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="fade-up" style="animation-delay:.15s">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6" style="position:sticky;top:100px;">
          <h3 class="font-semibold text-lg mb-5">Order Summary</h3>

          <div class="space-y-3 mb-5">
            <div style="display:flex;justify-content:space-between;font-size:0.95rem;color:#6B7280;">
              <span>Subtotal (<?= cartCount() ?> items)</span>
              <span><?= formatPrice($total) ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:0.95rem;color:#6B7280;">
              <span>Shipping</span>
              <span style="color:#16A34A;font-weight:600;">FREE</span>
            </div>
            <hr style="border:none;border-top:1px solid #E5E7EB;margin:1rem 0;">
            <div style="display:flex;justify-content:space-between;font-weight:700;font-size:1.25rem;">
              <span>Total</span>
              <span style="color:#4F46E5;"><?= formatPrice($total) ?></span>
            </div>
          </div>

          <?php if (isLoggedIn()): ?>
            <form method="POST">
              <input type="hidden" name="action" value="checkout">
              <button type="submit" class="form-btn">
                Place Order 🎉
              </button>
            </form>
          <?php else: ?>
            <a href="login.php" class="form-btn" style="display:block;text-align:center;text-decoration:none;">
              Sign in to Checkout
            </a>
            <p style="text-align:center;font-size:0.75rem;color:#6B7280;margin-top:1rem;">
              <a href="register.php" style="color:#4F46E5;text-decoration:none;">Create a free account</a>
            </p>
          <?php endif; ?>

          <div style="margin-top:1rem;text-align:center;font-size:0.75rem;color:#6B7280;display:flex;align-items:center;justify-content:center;gap:0.25rem;">
            🔒 Secure checkout
          </div>
        </div>
      </div>

    </div>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
