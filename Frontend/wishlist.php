<?php
session_start();

require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}

$productName = trim($_POST['product_name'] ?? '');

if ($productName === '') {
    header('Location: products.php');
    exit;
}

// Remember the selected product if login is required.
if (!isset($_SESSION['user_id'])) {
    $_SESSION['pending_wishlist_product_name'] = $productName;

    header('Location: login.php?redirect=wishlist_page.php');
    exit;
}

try {
    $stmt = $pdo->prepare(
        'SELECT product_id
         FROM products
         WHERE product_name = ? AND is_active = 1
         LIMIT 1'
    );
    $stmt->execute([$productName]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        exit('This product was not found in the database inventory.');
    }

    $userId = (int) $_SESSION['user_id'];
    $productId = (int) $product['product_id'];

    $stmt = $pdo->prepare(
        'SELECT 1 FROM wishlist
         WHERE user_id = ? AND product_id = ?
         LIMIT 1'
    );
    $stmt->execute([$userId, $productId]);

    if (!$stmt->fetchColumn()) {
        $stmt = $pdo->prepare(
            'INSERT INTO wishlist (user_id, product_id)
             VALUES (?, ?)'
        );
        $stmt->execute([$userId, $productId]);
    }

    header('Location: wishlist_page.php?added=1');
    exit;

} catch (PDOException $e) {
    error_log('Wishlist error: ' . $e->getMessage());
    http_response_code(500);
    exit('We could not update your wishlist. Please try again.');
}
