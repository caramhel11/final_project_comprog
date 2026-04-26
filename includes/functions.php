<?php
// includes/functions.php — Standardized helper functions

declare(strict_types=1);

require_once __DIR__ . '/db.php';

// ─── Session helpers ──────────────────────────────────────────────────────────

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => false, // set true on HTTPS
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['customer_id']);
}

function isAdminLoggedIn(): bool {
    startSession();
    return isset($_SESSION['admin_id']);
}

function currentCustomer(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id'   => $_SESSION['customer_id'],
        'name' => $_SESSION['customer_name'],
        'email'=> $_SESSION['customer_email'],
    ];
}

function requireLogin(string $redirect = 'login.php'): void {
    if (!isLoggedIn()) {
        header("Location: {$redirect}");
        exit;
    }
}

function requireAdminLogin(string $redirect = 'admin/login.php'): void {
    if (!isAdminLoggedIn()) {
        header("Location: {$redirect}");
        exit;
    }
}

// ─── Auth ─────────────────────────────────────────────────────────────────────

function registerCustomer(string $fullName, string $email, string $password): array {
    $pdo = db();

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT CustomerID FROM Customer_Table WHERE Email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email is already registered.'];
    }

    $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $pdo->prepare("INSERT INTO Customer_Table (FullName, Email, Password) VALUES (?, ?, ?)");
    $stmt->execute([$fullName, $email, $hashed]);

    return ['success' => true, 'message' => 'Account created successfully!'];
}

function loginCustomer(string $email, string $password): array {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM Customer_Table WHERE Email = ?");
    $stmt->execute([$email]);
    $customer = $stmt->fetch();

    if (!$customer || !password_verify($password, $customer['Password'])) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    startSession();
    session_regenerate_id(true);
    $_SESSION['customer_id']    = $customer['CustomerID'];
    $_SESSION['customer_name']  = $customer['FullName'];
    $_SESSION['customer_email'] = $customer['Email'];

    return ['success' => true, 'message' => 'Welcome back, ' . $customer['FullName'] . '!'];
}

function loginAdmin(string $username, string $password): array {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM Admin_Table WHERE Username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['Password'])) {
        return ['success' => false, 'message' => 'Invalid credentials.'];
    }

    startSession();
    session_regenerate_id(true);
    $_SESSION['admin_id']   = $admin['AdminID'];
    $_SESSION['admin_name'] = $admin['Username'];
    $_SESSION['admin_role'] = $admin['Role'];

    return ['success' => true, 'role' => $admin['Role']];
}

function logoutCustomer(): void {
    startSession();
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

function logoutAdmin(): void {
    startSession();
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . 'admin/login.php');
    exit;
}

// ─── Products ─────────────────────────────────────────────────────────────────

function getAllProducts(int $limit = 0, int $offset = 0): array {
    $pdo = db();
    $sql = "SELECT p.*, c.CategoryName
            FROM Products_Table p
            JOIN Category_Table c ON p.CategoryID = c.CategoryID
            WHERE p.StockQty > 0
            ORDER BY p.ProductID";

    if ($limit > 0) {
        $sql .= " LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }
    return $stmt->fetchAll();
}

function getProductById(int $id): ?array {
    $stmt = db()->prepare(
        "SELECT p.*, c.CategoryName
         FROM Products_Table p
         JOIN Category_Table c ON p.CategoryID = c.CategoryID
         WHERE p.ProductID = ?"
    );
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function getProductsByCategory(int $categoryId): array {
    $stmt = db()->prepare(
        "SELECT p.*, c.CategoryName
         FROM Products_Table p
         JOIN Category_Table c ON p.CategoryID = c.CategoryID
         WHERE p.CategoryID = ? AND p.StockQty > 0
         ORDER BY p.ProductName"
    );
    $stmt->execute([$categoryId]);
    return $stmt->fetchAll();
}

function searchProducts(string $query): array {
    $like = '%' . $query . '%';
    $stmt = db()->prepare(
        "SELECT p.*, c.CategoryName
         FROM Products_Table p
         JOIN Category_Table c ON p.CategoryID = c.CategoryID
         WHERE (p.ProductName LIKE ? OR p.Description LIKE ? OR c.CategoryName LIKE ?)
           AND p.StockQty > 0
         ORDER BY p.ProductName"
    );
    $stmt->execute([$like, $like, $like]);
    return $stmt->fetchAll();
}

function getAllCategories(): array {
    $stmt = db()->prepare("SELECT * FROM Category_Table ORDER BY CategoryName");
    $stmt->execute();
    return $stmt->fetchAll();
}

// ─── Cart (session-based) ─────────────────────────────────────────────────────

function addToCart(int $productId, int $qty = 1): void {
    startSession();
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $qty;
    } else {
        $_SESSION['cart'][$productId] = $qty;
    }
}

function removeFromCart(int $productId): void {
    startSession();
    unset($_SESSION['cart'][$productId]);
}

function updateCartQty(int $productId, int $qty): void {
    startSession();
    if ($qty <= 0) {
        removeFromCart($productId);
    } else {
        $_SESSION['cart'][$productId] = $qty;
    }
}

function getCartItems(): array {
    startSession();
    if (empty($_SESSION['cart'])) return [];

    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare(
        "SELECT p.*, c.CategoryName
         FROM Products_Table p
         JOIN Category_Table c ON p.CategoryID = c.CategoryID
         WHERE p.ProductID IN ({$placeholders})"
    );
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    foreach ($products as &$product) {
        $product['cart_qty'] = $_SESSION['cart'][$product['ProductID']] ?? 0;
        $product['subtotal'] = $product['Price'] * $product['cart_qty'];
    }
    return $products;
}

function cartTotal(): float {
    $items = getCartItems();
    return array_sum(array_column($items, 'subtotal'));
}

function cartCount(): int {
    startSession();
    if (empty($_SESSION['cart'])) return 0;
    return array_sum($_SESSION['cart']);
}

function clearCart(): void {
    startSession();
    $_SESSION['cart'] = [];
}

// ─── Orders ───────────────────────────────────────────────────────────────────

function placeOrder(int $customerId, float $total): array {
    $pdo = db();
    $items = getCartItems();
    if (empty($items)) {
        return ['success' => false, 'message' => 'Your cart is empty.'];
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            "INSERT INTO Order_Table (CustomerID, OrderDate, Status, TotalAmount)
             VALUES (?, NOW(), 'Pending', ?)"
        );
        $stmt->execute([$customerId, $total]);
        $orderId = (int) $pdo->lastInsertId();

        $detail = $pdo->prepare(
            "INSERT INTO OrderDetails_Table (OrderID, ProductID, Quantity, UnitPrice)
             VALUES (?, ?, ?, ?)"
        );
        $stock = $pdo->prepare(
            "UPDATE Products_Table SET StockQty = StockQty - ? WHERE ProductID = ? AND StockQty >= ?"
        );

        foreach ($items as $item) {
            $detail->execute([$orderId, $item['ProductID'], $item['cart_qty'], $item['Price']]);
            $stock->execute([$item['cart_qty'], $item['ProductID'], $item['cart_qty']]);
        }

        $pdo->commit();
        clearCart();
        return ['success' => true, 'order_id' => $orderId];

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Order failed: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Order could not be placed. Please try again.'];
    }
}

function getOrdersByCustomer(int $customerId): array {
    $stmt = db()->prepare(
        "SELECT o.*, COUNT(od.DetailID) AS item_count
         FROM Order_Table o
         LEFT JOIN OrderDetails_Table od ON o.OrderID = od.OrderID
         WHERE o.CustomerID = ?
         GROUP BY o.OrderID
         ORDER BY o.OrderDate DESC"
    );
    $stmt->execute([$customerId]);
    return $stmt->fetchAll();
}

// ─── Admin: Sales monitoring ───────────────────────────────────────────────────

function getAllOrders(): array {
    $stmt = db()->prepare(
        "SELECT o.*, c.FullName AS CustomerName, a.Username AS AdminUsername
         FROM Order_Table o
         JOIN Customer_Table c ON o.CustomerID = c.CustomerID
         LEFT JOIN Admin_Table a ON o.AdminID = a.AdminID
         ORDER BY o.OrderDate DESC"
    );
    $stmt->execute();
    return $stmt->fetchAll();
}

function getTotalRevenue(): float {
    $stmt = db()->prepare(
        "SELECT COALESCE(SUM(TotalAmount), 0) FROM Order_Table WHERE Status = 'Delivered'"
    );
    $stmt->execute();
    return (float) $stmt->fetchColumn();
}

function getTotalOrders(): int {
    $stmt = db()->prepare("SELECT COUNT(*) FROM Order_Table");
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

function getTotalCustomers(): int {
    $stmt = db()->prepare("SELECT COUNT(*) FROM Customer_Table");
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

function getLowStockProducts(int $threshold = 10): array {
    $stmt = db()->prepare(
        "SELECT * FROM Products_Table WHERE StockQty <= ? ORDER BY StockQty ASC"
    );
    $stmt->execute([$threshold]);
    return $stmt->fetchAll();
}

function updateOrderStatus(int $orderId, string $status): bool {
    $allowed = ['Pending','Processing','Shipped','Delivered','Cancelled'];
    if (!in_array($status, $allowed, true)) return false;
    $stmt = db()->prepare("UPDATE Order_Table SET Status = ? WHERE OrderID = ?");
    $stmt->execute([$status, $orderId]);
    return true;
}

function getAllAdminProducts(): array {
    $stmt = db()->prepare(
        "SELECT p.*, c.CategoryName
         FROM Products_Table p
         JOIN Category_Table c ON p.CategoryID = c.CategoryID
         ORDER BY p.ProductID"
    );
    $stmt->execute();
    return $stmt->fetchAll();
}

// ─── Utilities ────────────────────────────────────────────────────────────────

function formatPrice(float $amount): string {
    return '₱' . number_format($amount, 2);
}

function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header("Location: {$url}");
    exit;
}

function flashSet(string $key, string $message): void {
    startSession();
    $_SESSION['flash'][$key] = $message;
}

function flashGet(string $key): ?string {
    startSession();
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

// ─── Database shortcut ───────────────────────────────────────────────────────

// Product image placeholder (since we have no actual images)
function productImage(array $product): string {
    if (!empty($product['ImageURL'])) {
        return $product['ImageURL'];
    }
    
    // Try to find matching image file based on product name
    $productName = strtolower($product['ProductName']);
    $productName = preg_replace('/[^a-z0-9]+/', '_', $productName);
    $productName = trim($productName, '_');
    
    // Check common image extensions
    $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $imagesDir = __DIR__ . '/../images/';
    
    foreach ($extensions as $ext) {
        $imagePath = $imagesDir . $productName . '.' . $ext;
        if (file_exists($imagePath)) {
            return BASE_URL . 'images/' . $productName . '.' . $ext;
        }
        // Also try with 2.0 suffix
        $imagePath2 = $imagesDir . $productName . '2.0.' . $ext;
        if (file_exists($imagePath2)) {
            return BASE_URL . 'images/' . $productName . '2.0.' . $ext;
        }
    }
    
    // If no match found, use generic product image placeholder
    return BASE_URL . 'images/background.jpg';
}

// ─── Admin Product Management ─────────────────────────────────────────────────

function addProduct(string $name, int $categoryId, float $price, int $stockQty, string $imageUrl, string $description = ''): array {
    $pdo = db();
    
    // Validate inputs
    if (empty($name) || $price <= 0 || $categoryId <= 0) {
        return ['success' => false, 'message' => 'Invalid product data'];
    }
    
    try {
        $stmt = $pdo->prepare('
            INSERT INTO Products_Table (ProductName, CategoryID, Price, StockQty, ImageURL, Description)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$name, $categoryId, $price, $stockQty, $imageUrl, $description]);
        return ['success' => true, 'message' => 'Product added successfully!', 'productId' => $pdo->lastInsertId()];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error adding product: ' . $e->getMessage()];
    }
}

function updateProduct(int $productId, string $name, int $categoryId, float $price, int $stockQty, string $imageUrl, string $description = ''): array {
    $pdo = db();
    
    // Validate inputs
    if (empty($name) || $price <= 0 || $categoryId <= 0 || $productId <= 0) {
        return ['success' => false, 'message' => 'Invalid product data'];
    }
    
    try {
        $stmt = $pdo->prepare('
            UPDATE Products_Table 
            SET ProductName = ?, CategoryID = ?, Price = ?, StockQty = ?, ImageURL = ?, Description = ?
            WHERE ProductID = ?
        ');
        $stmt->execute([$name, $categoryId, $price, $stockQty, $imageUrl, $description, $productId]);
        return ['success' => true, 'message' => 'Product updated successfully!'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error updating product: ' . $e->getMessage()];
    }
}

function deleteProduct(int $productId): array {
    $pdo = db();
    
    try {
        $stmt = $pdo->prepare('DELETE FROM Products_Table WHERE ProductID = ?');
        $stmt->execute([$productId]);
        return ['success' => true, 'message' => 'Product deleted successfully!'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error deleting product: ' . $e->getMessage()];
    }
}

function handleProductImageUpload(): ?string {
    if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $file = $_FILES['product_image'];
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    // Validate MIME type
    if (!in_array($file['type'], $allowed)) {
        return null;
    }
    
    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return null;
    }
    
    // Generate filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = time() . '_' . uniqid() . '.' . $ext;
    $uploadDir = __DIR__ . '/../images/';
    $uploadPath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return 'images/' . $filename;
    }
    
    return null;
}