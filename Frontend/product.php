<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

$product = ($id !== false && $id !== null && $id > 0)
    ? ($products[$id] ?? null)
    : null;

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= $product
            ? e($product['name']) . ' | Afghan Antiques'
            : 'Product Not Found | Afghan Antiques' ?>
    </title>

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* Quantity label stays above the purchase buttons */
        .product-details-info .quantity-label {
            display: block;
            margin: 24px 0 12px;
            font-weight: 600;
        }

        /* Keep both forms together in one row */
        .product-details-info .product-actions {
            display: flex !important;
            flex-direction: row !important;
            justify-content: flex-start !important;
            align-items: center !important;
            gap: 12px !important;
            width: fit-content !important;
            max-width: 100%;
            margin: 0 !important;
            flex-wrap: wrap;
        }

        .product-details-info .add-to-cart-form {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 12px !important;
            width: auto !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .product-details-info .add-to-cart-form input[type="number"] {
            width: 120px;
            min-width: 80px;
            box-sizing: border-box;
            margin: 0;
        }

        .product-details-info .wishlist-form {
            display: flex !important;
            flex: 0 0 auto !important;
            width: auto !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .product-details-info .wishlist-button {
            min-height: 70px;
            box-sizing: border-box;
            padding: 12px 20px;
            border: 2px solid #8b653f;
            border-radius: 0;
            background: transparent;
            color: #8b653f;
            font: inherit;
            white-space: nowrap;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }

        .product-details-info .wishlist-button:hover {
            background: #65472f;
            color: #fff;
        }

        .product-details-info .wishlist-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @media (max-width: 700px) {
            .product-details-info .product-actions {
                flex-wrap: wrap;
            }
        }
    </style>
</head>

<body>

<header>
    <nav>
        <a href="index.php">Home</a>
        <a href="products.php">Products</a>
        <a href="about.php">About Us</a>
        <a href="cart.php">Cart</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </nav>
</header>

<main class="product-details-page">

    <?php if ($product): ?>

        <a class="back-link" href="products.php">
            &larr; Back to Collection
        </a>

        <section class="product-details">

            <div class="product-details-image">
                <img
                    src="<?= e($product['image']) ?>"
                    alt="<?= e($product['name']) ?>"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                >

                <div class="image-placeholder" style="display:none;">
                    Product image unavailable
                </div>
            </div>

            <div class="product-details-info">

                <p class="product-category">
                    <?= e($product['category']) ?>
                </p>

                <h1><?= e($product['name']) ?></h1>

                <p class="details-price">
                    $<?= number_format($product['price'], 2) ?>
                </p>

                <p class="details-description">
                    <?= e($product['description']) ?>
                </p>

                <p class="stock-status">
                    <?= $product['quantity'] > 0
                        ? 'Available in our collection'
                        : 'Currently unavailable' ?>
                </p>

                <label for="quantity" class="quantity-label">
                    Quantity
                </label>

                <div class="product-actions">

                    <form
                        action="cart.php"
                        method="post"
                        class="add-to-cart-form"
                    >
                        <input type="hidden" name="action" value="add">

                        <input
                            type="hidden"
                            name="product_id"
                            value="<?= (int) $id ?>"
                        >

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

                    <form
                        method="POST"
                        action="wishlist.php"
                        class="wishlist-form"
                    >
                        <input
                            type="hidden"
                            name="product_id"
                            value="<?= (int) $id ?>"
                        >

                        <button
                            type="submit"
                            class="wishlist-button"
                            <?= $product['quantity'] < 1 ? 'disabled' : '' ?>
                        >
                            ♡ Add to Wishlist
                        </button>
                    </form>

                </div>
            </div>
        </section>

    <?php else: ?>

        <section class="product-not-found">
            <h1>Product Not Found</h1>

            <p>
                This product does not exist in our current preview collection.
            </p>

            <a class="product-button" href="products.php">
                Browse Products
            </a>
        </section>

    <?php endif; ?>

</main>

<footer>
    <p>
        &copy; <?= date('Y') ?> Afghan Antiques. All rights reserved.
    </p>
</footer>

</body>
</html>
