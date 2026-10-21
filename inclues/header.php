
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Antique Store') ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>

<header class="site-header">
    <a href="index.php" class="brand">
        <span class="brand-mark">A</span>
        <span class="brand-text">
            <strong>Antique Store</strong>
            <small>TIMELESS TREASURES</small>
        </span>
    </a>

    <button class="menu-toggle" id="menuToggle"
            aria-label="Toggle navigation"
            aria-expanded="false">☰</button>

    <nav class="nav-links" id="navLinks">
        <a href="index.php">Home</a>
        <a href="products.php">Products</a>

        <?php if (isset($_SESSION['customer_id'])): ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="signup-button">Sign Up</a>
        <?php endif; ?>

        <a href="cart.php" class="cart-link">
            Cart <span class="cart-count">
                <?= (int)($_SESSION['cart_count'] ?? 0) ?>
            </span>
        </a>
    </nav>
</header>
