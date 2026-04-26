<?php
// shop.php — Full product listing with filter + search

require_once 'includes/functions.php';
startSession();

$pageTitle  = 'Shop — Isla Finds';
$categories = getAllCategories();

// Handle filter / search
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search     = isset($_GET['q']) ? trim($_GET['q']) : '';

if (!empty($search)) {
    $products = searchProducts($search);
} elseif ($categoryId > 0) {
    $products = getProductsByCategory($categoryId);
} else {
    $products = getAllProducts();
}

// Handle add-to-cart from this page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $pid = (int)($_POST['product_id'] ?? 0);
    if ($pid > 0) {
        addToCart($pid);
        flashSet('success', 'Item added to cart!');
    }
    // Re-build query string for redirect
    $qs = http_build_query(array_filter(['category' => $categoryId, 'q' => $search]));
    header('Location: shop.php' . ($qs ? "?{$qs}" : ''));
    exit;
}

$flash = flashGet('success');
include 'includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">

  <!-- Page header -->
  <div class="mb-8 fade-up">
    <span class="section-tag">Browse</span>
    <h1 class="section-title">Our Collection</h1>
    <p class="text-gray-500 mt-1 text-sm">
      <?= count($products) ?> product<?= count($products) !== 1 ? 's' : '' ?> found
      <?= $search ? 'for "' . sanitize($search) . '"' : '' ?>
    </p>
  </div>

  <?php if ($flash): ?>
    <div class="flash flash-success fade-up"><?= sanitize($flash) ?></div>
  <?php endif; ?>

  <!-- Search bar -->
  <form method="GET" class="mb-6 flex gap-3 fade-up">
    <input
      type="text"
      name="q"
      value="<?= sanitize($search) ?>"
      placeholder="Search products..."
      class="form-input flex-1 max-w-md"
    >
    <?php if ($categoryId): ?>
      <input type="hidden" name="category" value="<?= $categoryId ?>">
    <?php endif; ?>
    <button type="submit" class="btn-indigo">Search</button>
    <?php if ($search || $categoryId): ?>
      <a href="shop.php" class="btn-outline" style="border-color:#E5E7EB;color:#6B7280;">Clear</a>
    <?php endif; ?>
  </form>

  <!-- Category pills -->
  <div class="filter-pills fade-up">
    <a href="shop.php" class="filter-pill <?= $categoryId === 0 && !$search ? 'active' : '' ?>">
      All Products
    </a>
    <?php foreach ($categories as $cat): ?>
    <a href="shop.php?category=<?= $cat['CategoryID'] ?>"
       class="filter-pill <?= $categoryId === (int)$cat['CategoryID'] ? 'active' : '' ?>">
      <?= sanitize($cat['CategoryName']) ?>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Product grid -->
  <?php if (empty($products)): ?>
    <div class="text-center py-24 text-gray-400 fade-up">
      <div class="text-6xl mb-5">🔍</div>
      <h3 class="text-lg font-semibold text-gray-600 mb-2">No products found</h3>
      <p class="text-sm">Try a different search or browse all categories.</p>
      <a href="shop.php" class="btn-indigo mt-6 inline-flex">View All Products</a>
    </div>
  <?php else: ?>
    <div class="products-grid">
      <?php foreach ($products as $i => $product): ?>
      <div class="product-card fade-up" style="animation-delay:<?= $i * .05 ?>s">
        <div class="product-card-img-wrap">
          <img src="<?= productImage($product) ?>"
               alt="<?= sanitize($product['ProductName']) ?>"
               class="product-card-img"
               loading="lazy">
          <span class="product-category-tag"><?= sanitize($product['CategoryName']) ?></span>
        </div>
        <div class="product-card-body">
          <div class="product-name"><?= sanitize($product['ProductName']) ?></div>
          <div class="product-desc"><?= sanitize($product['Description'] ?? '') ?></div>
          <div class="flex items-end justify-between">
            <div>
              <div class="product-price"><?= formatPrice((float)$product['Price']) ?></div>
              <div class="product-stock"><?= (int)$product['StockQty'] ?> left in stock</div>
            </div>
          </div>
          <form method="POST">
            <input type="hidden" name="action"     value="add">
            <input type="hidden" name="product_id" value="<?= $product['ProductID'] ?>">
            <button type="submit" class="add-to-cart-btn">+ Add to Cart</button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>