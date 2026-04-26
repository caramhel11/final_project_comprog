<?php
// admin/dashboard.php — Sales monitoring & management

require_once '../includes/functions.php';
startSession();
requireAdminLogin();

$pageTitle = 'Dashboard — Isla Finds Admin';
$role      = $_SESSION['admin_role'] ?? 'Cashier';
$adminName = $_SESSION['admin_name'] ?? 'Admin';

// Stats
$totalRevenue   = getTotalRevenue();
$totalOrders    = getTotalOrders();
$totalCustomers = getTotalCustomers();
$lowStock       = getLowStockProducts(20);
$allOrders      = getAllOrders();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $status  = $_POST['status'] ?? '';
    updateOrderStatus($orderId, $status);
    header('Location: dashboard.php?tab=orders&updated=1');
    exit;
}

$tab      = $_GET['tab'] ?? 'overview';
$updated  = isset($_GET['updated']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body style="background:#F8F9FB;">

<!-- ── Sidebar ── -->
<aside class="admin-sidebar">
  <div class="mb-8 px-2">
    <div class="text-white font-bold text-xl" style="font-family:'Playfair Display',serif">
      Isla <span style="color:#D97706">Finds</span>
    </div>
    <div class="text-xs mt-1" style="color:rgba(255,255,255,.45)">Admin Portal</div>
  </div>

  <nav class="space-y-1 mb-8">
    <a href="?tab=overview"  class="admin-nav-item <?= $tab === 'overview'  ? 'active' : '' ?>">
      📊 &nbsp;Overview
    </a>
    <a href="?tab=orders"    class="admin-nav-item <?= $tab === 'orders'    ? 'active' : '' ?>">
      📦 &nbsp;Orders
    </a>
    <a href="?tab=products"  class="admin-nav-item <?= $tab === 'products'  ? 'active' : '' ?>">
      🛍️ &nbsp;Products
    </a>
    <a href="?tab=customers" class="admin-nav-item <?= $tab === 'customers' ? 'active' : '' ?>">
      👥 &nbsp;Customers
    </a>
  </nav>

  <div class="mt-auto pt-4 border-t border-white/10">
    <div class="px-3 py-2">
      <div class="text-white text-sm font-medium"><?= sanitize($adminName) ?></div>
      <div class="text-xs" style="color:rgba(255,255,255,.45)"><?= sanitize($role) ?></div>
    </div>
    <a href="logout.php" class="admin-nav-item text-red-300 mt-2">
      🚪 &nbsp;Logout
    </a>
  </div>
</aside>

<!-- ── Main Content ── -->
<main class="admin-content">

  <!-- Top bar -->
  <div class="flex items-center justify-between mb-8">
    <div>
      <h1 style="font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:700;color:#1E1B4B">
        <?php
          $titles = ['overview'=>'Dashboard Overview','orders'=>'Order Management',
                     'products'=>'Product Inventory','customers'=>'Customer Management'];
          echo $titles[$tab] ?? 'Dashboard';
        ?>
      </h1>
      <p class="text-sm text-gray-500"><?= date('l, F j, Y') ?></p>
    </div>
    <a href="../index.php" target="_blank"
       class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
      View Shop →
    </a>
  </div>

  <?php if ($updated): ?>
    <div class="flash flash-success mb-6">Order status updated successfully.</div>
  <?php endif; ?>

  <?php if (isset($_GET['added'])): ?>
    <div class="flash flash-success mb-6">✓ Product added successfully!</div>
  <?php endif; ?>

  <?php if (isset($_GET['updated']) && $_GET['updated'] !== '1' && isset($_GET['tab']) && $_GET['tab'] === 'products'): ?>
    <div class="flash flash-success mb-6">✓ Product updated successfully!</div>
  <?php endif; ?>

  <?php if (isset($_GET['deleted'])): ?>
    <div class="flash flash-success mb-6">✓ Product deleted successfully!</div>
  <?php endif; ?>

  <!-- ════ OVERVIEW TAB ════ -->
  <?php if ($tab === 'overview'): ?>

  <!-- Stat cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">

    <div class="admin-stat-card">
      <div class="text-3xl mb-1">💰</div>
      <div class="text-2xl font-bold" style="font-family:'Playfair Display',serif;color:#4338CA">
        <?= formatPrice($totalRevenue) ?>
      </div>
      <div class="text-sm text-gray-500 mt-1">Total Revenue (Delivered)</div>
    </div>

    <div class="admin-stat-card">
      <div class="text-3xl mb-1">📦</div>
      <div class="text-2xl font-bold" style="font-family:'Playfair Display',serif;color:#4338CA">
        <?= number_format($totalOrders) ?>
      </div>
      <div class="text-sm text-gray-500 mt-1">Total Orders</div>
    </div>

    <div class="admin-stat-card">
      <div class="text-3xl mb-1">👥</div>
      <div class="text-2xl font-bold" style="font-family:'Playfair Display',serif;color:#4338CA">
        <?= number_format($totalCustomers) ?>
      </div>
      <div class="text-sm text-gray-500 mt-1">Registered Customers</div>
    </div>
  </div>

  <!-- Recent orders preview -->
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
    <div class="flex justify-between items-center mb-5">
      <h3 class="font-semibold" style="font-family:'Playfair Display',serif">Recent Orders</h3>
      <a href="?tab=orders" class="text-sm text-indigo-600 hover:underline">View all →</a>
    </div>
    <div class="overflow-x-auto">
      <table style="width:100%;border-collapse:collapse;">
        <thead>
          <tr class="text-left text-xs text-gray-400 uppercase tracking-wider">
            <th class="pb-3 pr-4">Order ID</th>
            <th class="pb-3 pr-4">Customer</th>
            <th class="pb-3 pr-4">Date</th>
            <th class="pb-3 pr-4">Total</th>
            <th class="pb-3">Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (array_slice($allOrders, 0, 5) as $order): ?>
          <tr class="border-t border-gray-50">
            <td class="py-3 pr-4 text-sm font-mono text-indigo-600">#<?= $order['OrderID'] ?></td>
            <td class="py-3 pr-4 text-sm font-medium"><?= sanitize($order['CustomerName']) ?></td>
            <td class="py-3 pr-4 text-sm text-gray-500"><?= date('M j, Y', strtotime($order['OrderDate'])) ?></td>
            <td class="py-3 pr-4 text-sm font-semibold"><?= formatPrice((float)$order['TotalAmount']) ?></td>
            <td class="py-3">
              <span class="status-badge status-<?= $order['Status'] ?>"><?= $order['Status'] ?></span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Low stock alerts -->
  <?php if (!empty($lowStock)): ?>
  <div class="bg-white rounded-2xl border border-amber-200 shadow-sm p-6">
    <h3 class="font-semibold text-amber-700 mb-4" style="font-family:'Playfair Display',serif">
      ⚠️ Low Stock Alerts
    </h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
      <?php foreach ($lowStock as $p): ?>
      <div class="flex items-center justify-between bg-amber-50 border border-amber-100 rounded-xl px-4 py-3">
        <span class="text-sm font-medium text-gray-700"><?= sanitize($p['ProductName']) ?></span>
        <span class="text-sm font-bold text-amber-600 ml-3"><?= $p['StockQty'] ?> left</span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- ════ ORDERS TAB ════ -->
  <?php elseif ($tab === 'orders'): ?>
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
    <div class="overflow-x-auto">
      <table style="width:100%;border-collapse:collapse;">
        <thead>
          <tr class="text-left text-xs text-gray-400 uppercase tracking-wider border-b border-gray-100">
            <th class="pb-3 pr-4 pl-2">Order ID</th>
            <th class="pb-3 pr-4">Customer</th>
            <th class="pb-3 pr-4">Admin</th>
            <th class="pb-3 pr-4">Date</th>
            <th class="pb-3 pr-4">Total</th>
            <th class="pb-3 pr-4">Status</th>
            <th class="pb-3">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($allOrders as $order): ?>
          <tr class="border-b border-gray-50 hover:bg-gray-50/50">
            <td class="py-3 pr-4 pl-2 text-sm font-mono text-indigo-600">#<?= $order['OrderID'] ?></td>
            <td class="py-3 pr-4 text-sm font-medium"><?= sanitize($order['CustomerName']) ?></td>
            <td class="py-3 pr-4 text-sm text-gray-500"><?= sanitize($order['AdminUsername'] ?? '—') ?></td>
            <td class="py-3 pr-4 text-sm text-gray-500"><?= date('M j, Y', strtotime($order['OrderDate'])) ?></td>
            <td class="py-3 pr-4 text-sm font-semibold"><?= formatPrice((float)$order['TotalAmount']) ?></td>
            <td class="py-3 pr-4">
              <span class="status-badge status-<?= $order['Status'] ?>"><?= $order['Status'] ?></span>
            </td>
            <td class="py-3">
              <form method="POST" class="flex items-center gap-2">
                <input type="hidden" name="order_id"      value="<?= $order['OrderID'] ?>">
                <input type="hidden" name="update_status" value="1">
                <select name="status"
                        class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 outline-none focus:border-indigo-400"
                        style="font-family:'DM Sans',sans-serif">
                  <?php foreach (['Pending','Processing','Shipped','Delivered','Cancelled'] as $s): ?>
                    <option value="<?= $s ?>" <?= $order['Status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition">
                  Save
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ════ PRODUCTS TAB ════ -->
  <?php elseif ($tab === 'products'): ?>
  <?php $allProducts = getAllAdminProducts(); ?>
  
  <div class="flex items-center justify-between mb-6">
    <div></div>
    <a href="add_product.php" class="btn-primary inline-flex gap-2">
      ➕ Add New Product
    </a>
  </div>
  
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
    <div class="overflow-x-auto">
      <table style="width:100%;border-collapse:collapse;">
        <thead>
          <tr class="text-left text-xs text-gray-400 uppercase tracking-wider border-b border-gray-100">
            <th class="pb-3 pr-4 pl-2">ID</th>
            <th class="pb-3 pr-4">Product</th>
            <th class="pb-3 pr-4">Category</th>
            <th class="pb-3 pr-4">Price</th>
            <th class="pb-3 pr-4">Stock</th>
            <th class="pb-3 pr-4">Status</th>
            <th class="pb-3 pr-4 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($allProducts as $p): ?>
          <tr class="border-b border-gray-50 hover:bg-gray-50/50">
            <td class="py-3 pr-4 pl-2 text-xs text-gray-400"><?= $p['ProductID'] ?></td>
            <td class="py-3 pr-4 text-sm font-medium"><?= sanitize($p['ProductName']) ?></td>
            <td class="py-3 pr-4 text-sm text-gray-500"><?= sanitize($p['CategoryName']) ?></td>
            <td class="py-3 pr-4 text-sm font-semibold text-indigo-700"><?= formatPrice((float)$p['Price']) ?></td>
            <td class="py-3 pr-4 text-sm">
              <span class="font-bold <?= $p['StockQty'] <= 10 ? 'text-red-500' : ($p['StockQty'] <= 30 ? 'text-amber-500' : 'text-green-600') ?>">
                <?= $p['StockQty'] ?>
              </span>
            </td>
            <td class="py-3 pr-4">
              <?php if ($p['StockQty'] === 0): ?>
                <span class="status-badge status-Cancelled">Out of Stock</span>
              <?php elseif ($p['StockQty'] <= 10): ?>
                <span class="status-badge status-Pending">Low Stock</span>
              <?php else: ?>
                <span class="status-badge status-Delivered">In Stock</span>
              <?php endif; ?>
            </td>
            <td class="py-3 pr-4 text-center">
              <div class="flex gap-2 justify-center">
                <a href="edit_product.php?id=<?= $p['ProductID'] ?>"
                   class="text-xs px-3 py-1 rounded bg-blue-100 text-blue-700 hover:bg-blue-200 font-medium">
                  Edit
                </a>
                <form method="POST" action="delete_product.php" style="display:inline;"
                      onsubmit="return confirm('Are you sure you want to delete this product?');">
                  <input type="hidden" name="product_id" value="<?= $p['ProductID'] ?>">
                  <button type="submit" class="text-xs px-3 py-1 rounded bg-red-100 text-red-700 hover:bg-red-200 font-medium">
                    Delete
                  </button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ════ CUSTOMERS TAB ════ -->
  <?php elseif ($tab === 'customers'): ?>
  <?php
  $stmt = db()->prepare("SELECT CustomerID, FullName, Email FROM Customer_Table ORDER BY CustomerID");
  $stmt->execute();
  $customers = $stmt->fetchAll();
  ?>
  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
    <div class="overflow-x-auto">
      <table style="width:100%;border-collapse:collapse;">
        <thead>
          <tr class="text-left text-xs text-gray-400 uppercase tracking-wider border-b border-gray-100">
            <th class="pb-3 pr-4 pl-2">ID</th>
            <th class="pb-3 pr-4">Name</th>
            <th class="pb-3">Email</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($customers as $c): ?>
          <tr class="border-b border-gray-50 hover:bg-gray-50/50">
            <td class="py-3 pr-4 pl-2 text-xs text-gray-400"><?= $c['CustomerID'] ?></td>
            <td class="py-3 pr-4">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm font-bold">
                  <?= strtoupper(mb_substr($c['FullName'], 0, 1)) ?>
                </div>
                <span class="text-sm font-medium"><?= sanitize($c['FullName']) ?></span>
              </div>
            </td>
            <td class="py-3 text-sm text-gray-500"><?= sanitize($c['Email']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

</main>

</body>
</html>