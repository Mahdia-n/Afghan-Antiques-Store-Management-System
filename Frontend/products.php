<?php
session_start();

ini_set('display_errors', '1');
error_reporting(E_ALL);

$pageTitle = 'Our Collection | Afghan Antique Store';
$products = [];
$categories = [];
$dbError = false;

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');

function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

try {
    require_once __DIR__ . '/config/database.php';

    $categories = $pdo->query(
        "SELECT DISTINCT category
         FROM inventory
         WHERE category IS NOT NULL AND category <> ''
         ORDER BY category"
    )->fetchAll(PDO::FETCH_COLUMN);

    $sql = "SELECT product_id, product_name, description,
                   category, price, quantity, image
            FROM inventory
            WHERE 1=1";

    $params = [];

    if ($search !== '') {
        $sql .= " AND (product_name LIKE :search
                       OR description LIKE :search)";
        $params['search'] = "%$search%";
    }

    if ($category !== '') {
        $sql .= " AND category = :category";
        $params['category'] = $category;
    }

    $sql .= " ORDER BY product_name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

} catch (Throwable $ex) {
    error_log($ex->getMessage());
    $dbError = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
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

        <?php if (isset($_SESSION['customer_id'])): ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="signup-button">Sign Up</a>
        <?php endif; ?>

        <a href="cart.php" class="cart-link">Cart</a>
    </nav>
</header>

<main class="products-page">

    <section class="section-heading">
        <p class="eyebrow">EXPLORE OUR COLLECTION</p>
        <h1>Afghan Antiques & Treasures</h1>
        <p>
            Discover distinctive antiques, traditional craftsmanship,
            and treasures inspired by Afghan heritage.
        </p>
    </section>

    <form method="GET" action="products.php" class="product-filters">
        <input
            type="search"
            name="search"
            placeholder="Search antiques..."
            value="<?= e($search) ?>"
        >

        <select name="category">
            <option value="">All Categories</option>

            <?php foreach ($categories as $item): ?>
                <option
                    value="<?= e($item) ?>"
                    <?= $category === $item ? 'selected' : '' ?>
                >
                    <?= e($item) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="primary-button">Search</button>
        <a href="products.php" class="product-button">Clear</a>
    </form>

    <?php if ($dbError): ?>

        <div class="empty-products">
            <h2>Products are temporarily unavailable</h2>
            <p>We couldn't load the inventory. Please try again later.</p>
        </div>

    <?php elseif (empty($products)): ?>

        <div class="empty-products">
            <h2>No Products to Show Right Now</h2>
            <p>
                Our antique collection will appear here when products
                are added to the inventory. Please check back soon.
            </p>
        </div>

    <?php else: ?>

        <p class="results-count">
            <?= count($products) ?> product(s) found
        </p>

        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <article class="product-card">

                    <div class="product-art">
                        <?php if (!empty($product['image'])): ?>
                            <img
                                src="assets/images/<?= rawurlencode(basename($product['image'])) ?>"
                                alt="<?= e($product['product_name']) ?>"
                            >
                        <?php else: ?>
                            <div class="no-product-image">
                                Image unavailable
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="product-info">
                        <p class="product-category">
                            <?= e($product['category']) ?>
                        </p>

                        <h2><?= e($product['product_name']) ?></h2>

                        <p class="product-description">
                            <?= e($product['description']) ?>
                        </p>

                        <p class="product-price">
                            $<?= number_format((float)$product['price'], 2) ?>
                        </p>

                        <p class="stock-status <?= (int)$product['quantity'] < 1 ? 'out-of-stock' : '' ?>">
                            <?= (int)$product['quantity'] > 0
                                ? 'In stock'
                                : 'Out of stock' ?>
                        </p>

                        <a
                            href="product.php?id=<?= (int)$product['product_id'] ?>"
                            class="product-button"
                        >
                            View Details
                        </a>
                    </div>

                </article>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</main>

<footer class="site-footer">
    <div class="footer-bottom">
        &copy; <?= date('Y') ?> Afghan Antique Store. All rights reserved.
    </div>
</footer>

</body>
</html>
