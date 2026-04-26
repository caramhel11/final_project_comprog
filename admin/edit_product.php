<?php
// admin/edit_product.php — Edit product form

require_once '../includes/functions.php';
startSession();
requireAdminLogin();

$productId = (int)($_GET['id'] ?? 0);
if ($productId <= 0) {
    header('Location: dashboard.php?tab=products');
    exit;
}

$product = getProductById($productId);
if (!$product) {
    header('Location: dashboard.php?tab=products&error=Product not found');
    exit;
}

$error = '';
$success = '';
$categories = getAllCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['product_name'] ?? '');
    $categoryId  = (int)($_POST['category_id'] ?? 0);
    $price       = (float)($_POST['price'] ?? 0);
    $stockQty    = (int)($_POST['stock_qty'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $imageUrl    = $product['ImageURL'];
    
    // Handle image upload if a new image is provided
    if (!empty($_FILES['product_image']['name'])) {
        $newImageUrl = handleProductImageUpload();
        if (!$newImageUrl) {
            $error = 'Invalid image. Please upload a valid image file (JPEG, PNG, GIF, WebP) up to 5MB.';
        } else {
            $imageUrl = $newImageUrl;
        }
    }
    
    if (empty($error)) {
        if (empty($name)) {
            $error = 'Product name is required.';
        } elseif ($categoryId <= 0) {
            $error = 'Please select a category.';
        } elseif ($price <= 0) {
            $error = 'Price must be greater than 0.';
        } elseif ($stockQty < 0) {
            $error = 'Stock quantity cannot be negative.';
        } else {
            $result = updateProduct($productId, $name, $categoryId, $price, $stockQty, $imageUrl, $description);
            if ($result['success']) {
                header('Location: dashboard.php?tab=products&updated=1');
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Product — Isla Finds Admin</title>
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
    <a href="dashboard.php?tab=products" class="admin-nav-item">🛍️ &nbsp;Products</a>
  </nav>
  <div class="mt-auto pt-4 border-t border-white/10">
    <a href="logout.php" class="admin-nav-item text-red-300 mt-2">
      🚪 &nbsp;Logout
    </a>
  </div>
</aside>

<!-- ── Main Content ── -->
<main class="admin-content">
  <div class="flex items-center justify-between mb-8">
    <div>
      <h1 style="font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:700;color:#1E1B4B">
        Edit Product
      </h1>
      <p class="text-sm text-gray-500">Update product #<?= $productId ?></p>
    </div>
    <a href="dashboard.php?tab=products" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
      ← Back to Products
    </a>
  </div>

  <?php if ($error): ?>
    <div class="flash flash-error mb-6"><?= sanitize($error) ?></div>
  <?php endif; ?>

  <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 max-w-2xl">
    <form method="POST" enctype="multipart/form-data" class="space-y-6">
      
      <!-- Product Name -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Product Name *</label>
        <input type="text" name="product_name" required
               class="form-input w-full"
               placeholder="e.g., Arrowroot Cookies"
               value="<?= sanitize($product['ProductName']) ?>">
      </div>

      <!-- Category -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
        <select name="category_id" required class="form-input w-full">
          <option value="">-- Select Category --</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['CategoryID'] ?>"
                    <?= (int)$cat['CategoryID'] === (int)$product['CategoryID'] ? 'selected' : '' ?>>
              <?= sanitize($cat['CategoryName']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Price & Stock (Side by side) -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Price (₱) *</label>
          <input type="number" name="price" required step="0.01" min="0"
                 class="form-input w-full"
                 value="<?= (float)$product['Price'] ?>">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Stock Quantity *</label>
          <input type="number" name="stock_qty" required min="0"
                 class="form-input w-full"
                 value="<?= (int)$product['StockQty'] ?>">
        </div>
      </div>

      <!-- Description -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
        <textarea name="description" rows="4"
                  class="form-input w-full"
                  placeholder="Product description..."><?= sanitize($product['Description'] ?? '') ?></textarea>
      </div>

      <!-- Current Image -->
      <?php if (!empty($product['ImageURL'])): ?>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Current Image</label>
        <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-4">
          <img src="<?= BASE_URL . $product['ImageURL'] ?>" alt="<?= sanitize($product['ProductName']) ?>"
               class="w-24 h-24 object-cover rounded">
          <div class="text-sm text-gray-600">
            <p class="font-medium"><?= sanitize($product['ProductName']) ?></p>
            <p class="text-xs text-gray-500"><?= $product['ImageURL'] ?></p>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- New Image Upload -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Replace Image (Optional)</label>
        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-500 transition-colors">
          <input type="file" name="product_image" accept="image/*" id="product_image"
                 class="hidden"
                 onchange="document.getElementById('image-name').textContent = this.files[0]?.name || 'No file selected'">
          <label for="product_image" class="cursor-pointer">
            <div class="text-2xl mb-2">📷</div>
            <p class="text-sm text-gray-600">Click to upload or drag and drop</p>
            <p class="text-xs text-gray-500 mt-1">PNG, JPG, GIF or WebP (max 5MB)</p>
          </label>
          <p class="text-xs text-indigo-600 mt-3" id="image-name">No file selected</p>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="flex gap-3 pt-6 border-t">
        <button type="submit" class="btn-primary flex-1">
          ✓ Update Product
        </button>
        <a href="dashboard.php?tab=products" class="btn-outline flex-1 text-center">
          Cancel
        </a>
      </div>
    </form>
  </div>
</main>

</body>
</html>
