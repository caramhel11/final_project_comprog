<?php
// admin/delete_product.php — Delete product handler

require_once '../includes/functions.php';
startSession();
requireAdminLogin();

$productId = (int)($_POST['product_id'] ?? 0);

if ($productId <= 0) {
    header('Location: dashboard.php?tab=products&error=Invalid product');
    exit;
}

$result = deleteProduct($productId);

if ($result['success']) {
    header('Location: dashboard.php?tab=products&deleted=1');
} else {
    header('Location: dashboard.php?tab=products&error=' . urlencode($result['message']));
}
exit;
