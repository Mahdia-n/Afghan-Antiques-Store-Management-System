<?php
session_start();

$products = [
    1 => [
        'name' => 'Afghan Antique Mantel Clock',
        'category' => 'Home Decor',
        'description' => 'A timeless decorative clock inspired by traditional craftsmanship. This distinctive piece brings vintage character and warmth to your home.',
        'price' => 185.00,
        'quantity' => 4,
        'image' => 'assets/images/antique-mantel-clock.png'
    ],
    2 => [
        'name' => 'Traditional Ceramic Vase',
        'category' => 'Pottery',
        'description' => 'A beautiful ceramic piece inspired by Afghan artistic heritage. Display it as a centerpiece or as part of your antique collection.',
        'price' => 95.00,
        'quantity' => 6,
        'image' => 'assets/images/ceramic-vase.png'
    ],
    3 => [
        'name' => 'Vintage Wooden Cabinet',
        'category' => 'Furniture',
        'description' => 'A vintage-inspired wooden cabinet with a warm, traditional appearance, ideal for adding character to your living space.',
        'price' => 450.00,
        'quantity' => 2,
        'image' => 'assets/images/vintage-wooden-cabinet.png'
    ],
    4 => [
        'name' => 'Victorian Table Lamp',
        'category' => 'Lighting',
        'description' => 'An elegant vintage-style table lamp that adds timeless charm and a classic atmosphere to your interior.',
        'price' => 125.00,
        'quantity' => 3,
        'image' => 'assets/images/victorian-table-lamp.png'
    ]
];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$product = ($id !== false && $id !== null)
    ? ($products[$id] ?? null)
    : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $product
            ? htmlspecialchars($product['name']) . ' | Afghan Antiques'
            : 'Product Not Found | Afghan Antiques' ?>
    </title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header>
    <nav>
        <a href="index.php">Home</a>
        <a href="products.php">Products</a>
        <a href="about.php">About Us</a>
        <a href="cart.php">Cart</a>
        <a href="login.php">Login</a>
    </nav>
</header>

<main class="product-details-page">
    <?php if ($product): ?>

        <a class="back-link" href="products.php">&larr; Back to Collection</a>

        <section class="product-details">
            <div class="product-details-image">
                <img
                    src="<?= htmlspecialchars($product['image']) ?>"
                    alt="<?= htmlspecialchars($product['name']) ?>"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                >
                <div class="image-placeholder" style="display:none;">
                    Product image unavailable
                </div>
            </div>

            <div class="product-details-info">
                <p class="product-category">
                    <?= htmlspecialchars($product['category']) ?>
                </p>

                <h1><?= htmlspecialchars($product['name']) ?></h1>

                <p class="details-price">
                    $<?= number_format($product['price'], 2) ?>
                </p>

                <p class="details-description">
                    <?= htmlspecialchars($product['description']) ?>
                </p>

                <p class="stock-status">
                    <?= $product['quantity'] > 0
                        ? 'Available in our collection'
                        : 'Currently unavailable' ?>
                </p>

                <form action="cart.php" method="post" class="add-to-cart-form">
                    <input type="hidden" name="action" value="add">
                    <input
                        type="hidden"
                        name="product_id"
                        value="<?= (int) $id ?>"
                    >

                    <label for="quantity">Quantity</label>

                    <input
                        type="number"
                        id="quantity"
                        name="quantity"
                        value="1"
                        min="1"
                        max="<?= (int) $product['quantity'] ?>"
                        required
                        <?= $product['quantity'] < 1 ? 'disabled' : '' ?>
                    >

                    <button
                        type="submit"
                        name="add_to_cart"
                        value="1"
                        <?= $product['quantity'] < 1 ? 'disabled' : '' ?>
                    >
                        Add to Cart
                    </button>
                </form>

                <p class="demo-note">
                    Preview mode: product information is temporary sample data.
                </p>
            </div>
        </section>

    <?php else: ?>

        <section class="product-not-found">
            <h1>Product Not Found</h1>
            <p>This product does not exist in our current preview collection.</p>
            <a class="product-button" href="products.php">Browse Products</a>
        </section>

    <?php endif; ?>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> Afghan Antiques. All rights reserved.</p>
</footer>

</body>
</html>
