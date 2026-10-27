<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=checkout.php');
    exit;
}

function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    header('Location: cart.php');
    exit;
}

require_once __DIR__ . '/config/database.php';

$products = [];
$productIds = array_map('intval', array_keys($cart));
$placeholders = implode(',', array_fill(0, count($productIds), '?'));

$stmt = $pdo->prepare(
    "SELECT product_id, product_name, price, quantity
     FROM products
     WHERE is_active = 1 AND product_id IN ($placeholders)"
);
$stmt->execute($productIds);

foreach ($stmt->fetchAll() as $product) {
    $products[$product['product_id']] = $product;
}

$subtotal = 0;

foreach ($cart as $id => $qty) {
    $id = (int)$id;
    $qty = (int)$qty;

    if (!isset($products[$id]) || $qty < 1 ||
        $qty > (int)$products[$id]['quantity']) {
        exit('A cart item is unavailable or has insufficient stock. Please return to your cart.');
    }

    $subtotal += (float)$products[$id]['price'] * $qty;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = 'The checkout form is ready, but order submission has not been connected yet.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Afghan Antique Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="site-header">
    <a href="index.php" class="brand">
        <span class="brand-mark">A</span>
        <span>
            <span class="brand-name">Afghan Antique Store</span>
            <span class="brand-tagline">HERITAGE & CRAFTSMANSHIP</span>
        </span>
    </a>
    <nav class="nav-links">
        <a href="index.php">Home</a>
        <a href="products.php">Products</a>
        <a href="cart.php">Cart</a>
    </nav>
</header>

<main class="auth-page">
    <section class="auth-container">
        <div class="auth-card">
            <p class="auth-eyebrow">ALMOST THERE</p>
            <h1>Checkout</h1>
            <p class="auth-subtitle">Enter your delivery information.</p>

            <?php if ($error !== ''): ?>
                <p class="auth-message"><?= e($error) ?></p>
            <?php endif; ?>

            <h2>Order Summary</h2>

            <?php foreach ($cart as $id => $qty): ?>
                <?php $product = $products[(int)$id]; ?>
                <p>
                    <?= e($product['product_name']) ?>
                    × <?= (int)$qty ?>
                    — $<?= number_format((float)$product['price'] * (int)$qty, 2) ?>
                </p>
            <?php endforeach; ?>

            <h3>Subtotal: $<?= number_format($subtotal, 2) ?></h3>

            <form method="POST" class="auth-form">
                <label for="phone">Phone number</label>
                <input id="phone" type="tel" name="phone" required>

                <label for="address">Shipping address</label>
                <input id="address" type="text" name="shipping_address" required>

                <label for="city">City</label>
                <input id="city" type="text" name="shipping_city" required>

                <label for="state">State</label>
                <input id="state" type="text" name="shipping_state">

                <label for="postal">ZIP / Postal code</label>
                <input id="postal" type="text" name="shipping_postal_code" required>

                <button type="submit" class="auth-submit">
                    Continue to Place Order
                </button>
            </form>

            <p class="auth-switch">
                <a href="cart.php">← Return to Cart</a>
            </p>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="footer-bottom">
        &copy; <?= date('Y') ?> Afghan Antique Store. All rights reserved.
    </div>
</footer>

</body>
</html>
