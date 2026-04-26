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
            if ($pid > 0) { addToCart($pid); flashSet('success', 'Added to cart!'); }
            break;

        case 'update':
            $pid = (int)($_POST['product_id'] ?? 0);
            $qty = (int)($_POST['qty']        ?? 0);
            if ($pid > 0) updateCartQty($pid, $qty);
            break;

        case 'remove':
            $pid = (int)($_POST['product_id'] ?? 0);
            if ($pid > 0) removeFromCart($pid);
            break;

        case 'checkout':
            requireLogin();
            $customer = currentCustomer();
            $total    = cartTotal();
            $result   = placeOrder($customer['id'], $total);
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
$total     = cartTotal();
$flash     = flashGet('success') ?? flashGet('error');

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
                  <form method="POST" class="qty-control">
                    <input type="hidden" name="action"     value="update">
                    <input type="hidden" name="product_id" value="<?= $item['ProductID'] ?>">
                    <button type="submit" name="qty" value="<?= $item['cart_qty'] - 1 ?>"
                            class="qty-btn">−</button>
                    <span class="text-sm font-semibold w-6 text-center"><?= $item['cart_qty'] ?></span>
                    <button type="submit" name="qty" value="<?= $item['cart_qty'] + 1 ?>"
                            class="qty-btn">+</button>
                  </form>
                </td>

                <td>
                  <span class="font-bold text-gray-800"><?= formatPrice($item['subtotal']) ?></span>
                </td>

                <td>
                  <form method="POST">
                    <input type="hidden" name="action"     value="remove">
                    <input type="hidden" name="product_id" value="<?= $item['ProductID'] ?>">
                    <button type="submit" class="text-red-400 hover:text-red-600 transition text-lg"
                            title="Remove">×</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="mt-4">
          <a href="shop.php" class="text-indigo-600 text-sm font-medium hover:text-indigo-800 transition">
            ← Continue Shopping
          </a>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="fade-up" style="animation-delay:.15s">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sticky top-24">
          <h3 class="font-semibold text-lg mb-5" style="font-family:'Playfair Display',serif">Order Summary</h3>

          <div class="space-y-3 mb-5">
            <div class="flex justify-between text-sm text-gray-600">
              <span>Items (<?= cartCount() ?>)</span>
              <span><?= formatPrice($total) ?></span>
            </div>
            <div class="flex justify-between text-sm text-gray-600">
              <span>Shipping</span>
              <span class="text-green-600 font-medium">Free</span>
            </div>
            <hr class="border-gray-100">
            <div class="flex justify-between font-bold text-lg">
              <span>Total</span>
              <span class="text-indigo-700"><?= formatPrice($total) ?></span>
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
            <a href="login.php" class="form-btn block text-center" style="text-decoration:none;">
              Sign in to Checkout
            </a>
            <p class="text-center text-xs text-gray-400 mt-3">
              <a href="register.php" class="text-indigo-500 hover:underline">Create a free account</a>
            </p>
          <?php endif; ?>

          <div class="mt-4 text-center text-xs text-gray-400 flex items-center justify-center gap-1">
            🔒 Secure checkout
          </div>
        </div>
      </div>

    </div>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>