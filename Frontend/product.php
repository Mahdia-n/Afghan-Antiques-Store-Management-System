<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
require_once __DIR__ . '/config/database.php';

// Escape output safely
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Get product ID from URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$product = null;
$databaseError = false;

// Retrieve product from MySQL
if ($id !== false && $id !== null && $id > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT
                p.product_id,
                p.product_name,
                p.description,
                p.price,
                p.quantity,
                p.image,
                p.is_active,
                c.category_name
            FROM products p
            LEFT JOIN categories c
                ON p.category_id = c.category_id
            WHERE p.product_id = ?
              AND p.is_active = 1
            LIMIT 1
        ");

        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log('Product details error: ' . $e->getMessage());
        $databaseError = true;
    }
}

// Prepare product information
if ($product) {
    $productName = $product['product_name'];
    $productCategory = $product['category_name'] ?? 'Antiques';
    $productDescription = $product['description'] ?? '';
    $productPrice = (float)$product['price'];
    $productQuantity = (int)$product['quantity'];
    $productImage = $product['image'] ?? '';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= $product
            ? e($productName) . ' | Afghan Antiques'
            : 'Product Not Found | Afghan Antiques' ?>
    </title>

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .product-details-info .quantity-label {
            display: block;
            margin: 24px 0 12px;
            font-weight: 600;
        }

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
            min-height: 50px;
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

        .product-details-info button:disabled,
        .product-details-info input:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @media (max-width: 700px) {
            .product-details-info .product-actions {
                width: 100% !important;
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

        <a href="cart.php">
            Cart (<?= isset($_SESSION['cart'])
                ? (int)array_sum($_SESSION['cart'])
                : 0 ?>)
        </a>

        <a href="wishlist_page.php">My Wishlist</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </nav>
</header>

<main class="product-details-page">

    <?php if ($databaseError): ?>

        <section class="product-not-found">
            <h1>Unable to Load Product</h1>
            <p>We are currently unable to retrieve this product. Please try again later.</p>

            <a class="product-button" href="products.php">
                Browse Products
            </a>
        </section>

    <?php elseif ($product): ?>

        <a class="back-link" href="products.php">
            &larr; Back to Collection
        </a>

        <section class="product-details">

            <div class="product-details-image">

                <?php if (!empty($productImage)): ?>

                    <img
                        src="<?= e($productImage) ?>"
                        alt="<?= e($productName) ?>"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                    >

                    <div class="image-placeholder" style="display:none;">
                        Product image unavailable
                    </div>

                <?php else: ?>

                    <div class="image-placeholder">
                        Product image unavailable
                    </div>

                <?php endif; ?>

            </div>

            <div class="product-details-info">

                <p class="product-category">
                    <?= e($productCategory) ?>
                </p>

                <h1><?= e($productName) ?></h1>

                <p class="details-price">
                    $<?= number_format($productPrice, 2) ?>
                </p>

                <p class="details-description">
                    <?= nl2br(e($productDescription)) ?>
                </p>

                <p class="stock-status">
                    <?= $productQuantity > 0
                        ? 'Available in our collection'
                        : 'Currently unavailable' ?>
                </p>

                <label for="quantity" class="quantity-label">
                    Quantity
                </label>

                <div class="product-actions">

                    <!-- Add to Cart -->
                    <form
                        action="cart.php"
                        method="POST"
                        class="add-to-cart-form"
                    >

                        <input type="hidden" name="action" value="add">

                        <input
                            type="hidden"
                            name="product_id"
                            value="<?= (int)$product['product_id'] ?>"
                        >

                        <input
                            type="number"
                            id="quantity"
                            name="quantity"
                            value="1"
                            min="1"
                            max="<?= $productQuantity ?>"
                            required
                            <?= $productQuantity < 1 ? 'disabled' : '' ?>
                        >

                        <button
                            type="submit"
                            <?= $productQuantity < 1 ? 'disabled' : '' ?>
                        >
                            Add to Cart
                        </button>

                    </form>

                    <!-- Add to Wishlist -->
                    <form
                        method="POST"
                        action="wishlist.php"
                        class="wishlist-form"
                    >

                        <input
                            type="hidden"
                            name="product_name"
                            value="<?= e($productName) ?>"
                        >

                        <button
                            type="submit"
                            class="wishlist-button"
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
                This product does not exist or is currently unavailable
                in our collection.
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
