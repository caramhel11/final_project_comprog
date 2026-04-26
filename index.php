<?php
// index.php — Isla Finds Landing Page

require_once 'includes/functions.php';
startSession();

$pageTitle  = 'Isla Finds — Marinduque Souvenir Shop';
$featuredProducts = getAllProducts(8); // Show 8 on landing
$categories = getAllCategories();

include 'includes/header.php';
?>

<!-- ── Hero Section ────────────────────────────────────── -->
<section class="hero py-28 px-4 sm:px-8 text-center relative" style="min-height:520px;display:flex;align-items:center;justify-content:center;">
  <div class="relative z-10 max-w-3xl mx-auto">
    <div class="hero-badge">
      🌺 &nbsp;Authentic Marinduque Finds
    </div>
    <h1 class="hero-title mb-5">
      Discover the Soul<br>of <em style="font-style:italic;color:#FCD34D">Marinduque</em>
    </h1>
    <p class="hero-subtitle max-w-xl mx-auto mb-8">
      Handcrafted souvenirs, island delicacies, and artisan goods — delivered straight
      from the heart of our beloved island province.
    </p>
    <div class="flex flex-wrap gap-3 justify-center">
      <a href="shop.php" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
        </svg>
        Shop Now
      </a>
      <a href="#featured" class="btn-outline">
        Explore Products
      </a>
    </div>
  </div>

  <!-- Wave SVG -->
  <svg class="hero-waves" viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M0 40C240 80 480 0 720 40C960 80 1200 0 1440 40V80H0V40Z" fill="#FAFAFA"/>
  </svg>
</section>

<!-- ── Stats Strip ─────────────────────────────────────── -->
<section class="stats-strip">
  <div class="max-w-5xl mx-auto grid grid-cols-2 md:grid-cols-4">
    <div class="stat-item border-r border-white/10">
      <span class="stat-number">17+</span>
      <div class="stat-label">Products</div>
    </div>
    <div class="stat-item md:border-r border-white/10">
      <span class="stat-number">4</span>
      <div class="stat-label">Categories</div>
    </div>
    <div class="stat-item border-r border-white/10">
      <span class="stat-number">100%</span>
      <div class="stat-label">Authentic</div>
    </div>
    <div class="stat-item">
      <span class="stat-number">🌺</span>
      <div class="stat-label">Island-Made</div>
    </div>
  </div>
</section>

<!-- ── Category Showcase ───────────────────────────────── -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-20">
  <div class="text-center mb-12 fade-up">
    <span class="section-tag">Browse by Type</span>
    <h2 class="section-title">Shop by Category</h2>
  </div>

  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <?php
    $catIcons = [1 => '🍪', 2 => '👕', 3 => '🧺', 4 => '🎁'];
    $catColors = [
      1 => 'from-amber-50 to-orange-100 border-orange-200',
      2 => 'from-blue-50 to-indigo-100 border-indigo-200',
      3 => 'from-green-50 to-emerald-100 border-green-200',
      4 => 'from-pink-50 to-rose-100 border-rose-200',
    ];
    foreach ($categories as $i => $cat):
      $icon  = $catIcons[$cat['CategoryID']]  ?? '🛍️';
      $color = $catColors[$cat['CategoryID']] ?? 'from-gray-50 to-gray-100 border-gray-200';
    ?>
    <a href="shop.php?category=<?= $cat['CategoryID'] ?>"
       class="fade-up bg-gradient-to-br <?= $color ?> border rounded-2xl p-6 text-center hover:scale-105 transition-transform duration-300 no-underline block"
       style="animation-delay:<?= $i * .08 ?>s">
      <div class="text-4xl mb-3"><?= $icon ?></div>
      <h3 class="font-semibold text-gray-800 text-sm"><?= sanitize($cat['CategoryName']) ?></h3>
    </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- ── Featured Products ───────────────────────────────── -->
<section id="featured" class="bg-white py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4 fade-up">
      <div>
        <span class="section-tag">Handpicked for You</span>
        <h2 class="section-title">Featured Products</h2>
      </div>
      <a href="shop.php" class="btn-indigo whitespace-nowrap">View All →</a>
    </div>

    <?php if (empty($featuredProducts)): ?>
      <div class="text-center py-16 text-gray-400">
        <div class="text-5xl mb-4">🌊</div>
        <p>Products are on their way from the island. Check back soon!</p>
      </div>
    <?php else: ?>
    <div class="products-grid">
      <?php foreach ($featuredProducts as $i => $product): ?>
      <div class="product-card fade-up" style="animation-delay:<?= $i * .07 ?>s">
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
              <div class="product-stock"><?= (int)$product['StockQty'] ?> in stock</div>
            </div>
          </div>
          <form method="POST" action="cart.php">
            <input type="hidden" name="action"     value="add">
            <input type="hidden" name="product_id" value="<?= $product['ProductID'] ?>">
            <button type="submit" class="add-to-cart-btn">
              + Add to Cart
            </button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ── Island Story Banner ─────────────────────────────── -->
<section class="py-20 px-4 sm:px-6 fade-up">
  <div class="max-w-5xl mx-auto">
    <div class="rounded-3xl overflow-hidden relative" style="background:linear-gradient(135deg,#1E1B4B,#4338CA);padding:3rem 2.5rem;">
      <div class="relative z-10 grid md:grid-cols-2 gap-8 items-center">
        <div>
          <span class="section-tag" style="background:rgba(255,255,255,.15);color:#fff;">Our Story</span>
          <h2 style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;color:#fff;margin-top:.5rem;line-height:1.2">
            Where Every Product Tells an Island Tale
          </h2>
          <p class="mt-4 text-sm leading-relaxed" style="color:rgba(255,255,255,.7)">
            From the arrowroot fields to the weaving looms of Marinduque, every item in Isla Finds
            is a piece of our culture — made by local hands, rooted in tradition, and delivered with love.
          </p>
          <a href="register.php" class="btn-primary mt-6 inline-flex">Create an Account →</a>
        </div>
        <div class="text-center text-6xl grid grid-cols-3 gap-4">
          <span>🌺</span><span>🧺</span><span>🍪</span>
          <span>🎁</span><span>🌊</span><span>👕</span>
          <span>🏝️</span><span>🕯️</span><span>🧃</span>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>