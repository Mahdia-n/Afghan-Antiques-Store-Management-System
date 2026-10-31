<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Admin-only access
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'admin'
) {
    http_response_code(403);
    exit('Access denied. Administrator access required.');
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

// Verify CSRF token
$token = $_POST['csrf_token'] ?? '';

if (
    !is_string($token) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    http_response_code(403);
    exit('Invalid security token. Please return to inventory and try again.');
}

// Validate product ID
$productId = filter_input(
    INPUT_POST,
    'product_id',
    FILTER_VALIDATE_INT
);

if (!$productId || $productId < 1) {
    http_response_code(400);
    exit('Invalid product ID.');
}

try {

    // Check whether the product exists and is active
    $stmt = $pdo->prepare(
        "SELECT product_id
         FROM products
         WHERE product_id = ? AND is_active = 1
         LIMIT 1"
    );

    $stmt->execute([$productId]);

    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        header('Location: products.php?not_found=1');
        exit;
    }

    // Soft delete the product
    $stmt = $pdo->prepare(
        "UPDATE products
         SET is_active = 0
         WHERE product_id = ?"
    );

    $stmt->execute([$productId]);

    header('Location: products.php?deleted=1');
    exit;

} catch (PDOException $e) {

    error_log('Delete product error: ' . $e->getMessage());

    http_response_code(500);
    exit('Could not delete the product. Please try again.');
}
