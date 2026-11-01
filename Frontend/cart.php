<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';

// Escape output safely
function escapeHtml($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Initialize cart
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Store a message and redirect after POST
function redirectCart(string $type, string $message): void
{
    $_SESSION['cart_flash'] = [
        'type' => $type,
        'message' => $message
    ];

    header('Location: cart.php');
    exit;
}

// Retrieve flash message
$flash = $_SESSION['cart_flash'] ?? null;
unset($_SESSION['cart_flash']);

$message = '';
$error = '';

if (is_array($flash)) {
    if (($flash['type'] ?? '') === 'success') {
        $message = $flash['message'] ?? '';
    } else {
        $error = $flash['message'] ?? '';
    }
}

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

    if ($action === 'add') {

        $quantity = filter_input(
            INPUT_POST,
            'quantity',
            FILTER_VALIDATE_INT
        );

        if ($id === false || $id === null || $id < 1) {
            redirectCart('error', 'Invalid product selected.');
        }

        if ($quantity === false || $quantity === null || $quantity < 1) {
            redirectCart('error', 'Please enter a valid quantity.');
        }

        try {
            // Get product directly from database
            $stmt = $pdo->prepare("
                SELECT product_id, product_name, quantity
                FROM products
                WHERE product_id = ?
                  AND is_active = 1
                LIMIT 1
            ");

            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                redirectCart('error', 'This product is no longer available.');
            }

            $availableStock = (int)$product['quantity'];
            $currentQuantity = (int)($_SESSION['cart'][$id] ?? 0);

            if ($availableStock <= 0) {
                redirectCart('error', 'This product is currently out of stock.');
            }

            $requestedQuantity = $currentQuantity + $quantity;

            if ($requestedQuantity > $availableStock) {
                redirectCart(
                    'error',
                    'Only ' . $availableStock . ' item(s) are available in stock.'
                );
            }

            if ($requestedQuantity > 99) {
                redirectCart('error', 'You cannot add more than 99 items.');
            }

            $_SESSION['cart'][$id] = $requestedQuantity;

            redirectCart(
                'success',
                $product['product_name'] . ' added to your cart.'
            );

        } catch (PDOException $e) {
            error_log('Cart add error: ' . $e->getMessage());

            redirectCart(
                'error',
                'Unable to add this product. Please try again.'
            );
        }

    } elseif ($action === 'update') {

        $quantity = filter_input(
            INPUT_POST,
            'quantity',
            FILTER_VALIDATE_INT
        );

        if ($id === false || $id === null || $id < 1) {
            redirectCart('error', 'Invalid product selected.');
        }

        if ($quantity === false || $quantity === null
            || $quantity < 1 || $quantity > 99) {
            redirectCart(
                'error',
                'Please enter a quantity between 1 and 99.'
            );
        }

        try {
            $stmt = $pdo->prepare("
                SELECT product_id, product_name, quantity
                FROM products
                WHERE product_id = ?
                  AND is_active = 1
                LIMIT 1
            ");

            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                unset($_SESSION['cart'][$id]);

                redirectCart(
                    'error',
                    'This product is no longer available and was removed from your cart.'
                );
            }

            $availableStock = (int)$product['quantity'];

            if ($availableStock <= 0) {
                unset($_SESSION['cart'][$id]);

                redirectCart(
                    'error',
                    'This product is out of stock and was removed from your cart.'
                );
            }

            if ($quantity > $availableStock) {
                redirectCart(
                    'error',
                    'Only ' . $availableStock . ' item(s) are available in stock.'
                );
            }

            $_SESSION['cart'][$id] = $quantity;

            redirectCart('success', 'Cart quantity updated.');

        } catch (PDOException $e) {
            error_log('Cart update error: ' . $e->getMessage());

            redirectCart(
                'error',
                'Unable to update your cart. Please try again.'
            );
        }

    } elseif ($action === 'remove') {

        if ($id === false || $id === null || $id < 1) {
            redirectCart('error', 'Invalid product selected.');
        }

        unset($_SESSION['cart'][$id]);

        redirectCart('success', 'Item removed from your cart.');

    } else {

        redirectCart('error', 'Invalid cart action.');
    }
}

// Build a clean list of product IDs in the cart
$productIds = [];

foreach ($_SESSION['cart'] as $id => $quantity) {
    $id = filter_var($id, FILTER_VALIDATE_INT);
    $quantity = filter_var($quantity, FILTER_VALIDATE_INT);

    if ($id !== false && $id !== null && $id > 0
        && $quantity !== false && $quantity !== null
        && $quantity > 0) {

        $productIds[] = $id;

    } else {
        unset($_SESSION['cart'][(string)$id]);
    }
}

// Retrieve all cart products from MySQL
$cartItems = [];
$subtotal = 0;
$cartCount = 0;
$databaseError = false;

if (!empty($productIds)) {

    try {
        $placeholders = implode(
            ',',
            array_fill(0, count($productIds), '?')
        );

        $stmt = $pdo->prepare("
            SELECT
                p.product_id,
                p.product_name,
                p.description,
                p.price,
                p.quantity,
                p.image,
                p.is_active
            FROM products p
            WHERE p.product_id IN ($placeholders)
              AND p.is_active = 1
        ");

        $stmt->execute($productIds);

        $databaseProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $productsById = [];

        foreach ($databaseProducts as $product) {
            $productsById[(int)$product['product_id']] = $product;
        }

        // Build cart display items
        foreach ($_SESSION['cart'] as $id => $quantity) {

            $id = (int)$id;
            $quantity = (int)$quantity;

            // Remove products that no longer exist or are inactive
            if (!isset($productsById[$id])) {
                unset($_SESSION['cart'][$id]);
                continue;
            }

            $product = $productsById[$id];
            $availableStock = (int)$product['quantity'];

            // Remove products that are out of stock
            if ($availableStock <= 0) {
                unset($_SESSION['cart'][$id]);
                continue;
            }

            // Adjust cart quantity if stock has decreased
            if ($quantity > $availableStock) {
                $quantity = $availableStock;
                $_SESSION['cart'][$id] = $quantity;

                if ($message === '' && $error === '') {
                    $message = 'Some cart quantities were adjusted to match available stock.';
                }
            }

            $price = (float)$product['price'];
            $itemTotal = $price * $quantity;

            $cartItems[] = [
                'id' => $id,
                'name' => $product['product_name'],
                'price' => $price,
                'image' => $product['image'] ?? '',
                'quantity' => $quantity,
                'total' => $itemTotal
            ];

            $subtotal += $itemTotal;
            $cartCount += $quantity;
        }

    } catch (PDOException $e) {
        error_log('Cart retrieval error: ' . $e->getMessage());
        $databaseError = true;
    }
}

// Checkout destination
$checkoutUrl = isset($_SESSION['user_id'])
    ? 'checkout.php'
    : 'login.php?redirect=checkout.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Shopping Cart | Afghan Antiques</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .cart-container {
            max-width: 1150px;
            margin: 45px auto;
            padding: 0 20px;
        }

        .cart-layout {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(260px, 1fr);
            gap: 30px;
            align-items: start;
        }

        .cart-item {
            display: flex;
            gap: 20px;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #e5e0d8;
        }

        .cart-item img {
            width: 125px;
            height: 125px;
            object-fit: contain;
            background: #f8f5f0;
            border-radius: 6px;
        }

        .cart-item-details {
            flex: 1;
            min-width: 0;
        }

        .cart-item-details h2 {
            font-size: 19px;
            margin: 0 0 8px;
        }

        .cart-item-price {
            font-weight: bold;
            margin: 8px 0;
        }

        .cart-item form {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            margin-top: 8px;
        }

        .cart-item input[type="number"] {
            width: 65px;
            padding: 8px;
        }

        .cart-button {
            display: inline-block;
            padding: 9px 13px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .cart-update {
            background: #eee8e1;
            color: #35291f;
        }

        .cart-remove {
            background: #f5e7e3;
            color: #8b3025;
        }

        .cart-summary {
            padding: 25px;
            background: #f8f5f0;
            border-radius: 8px;
        }

        .cart-summary h2 {
            margin-top: 0;
        }

        .cart-summary-row {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin: 18px 0;
        }

        .cart-primary-button {
            display: block;
            width: 100%;
            box-sizing: border-box;
            text-align: center;
            padding: 13px 16px;
            border-radius: 4px;
            background: #55402f;
            color: white;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .cart-secondary-button {
            display: inline-block;
            margin-top: 20px;
            color: #55402f;
            text-decoration: none;
        }

        .cart-empty {
            text-align: center;
            padding: 45px 20px;
            background: #f8f5f0;
            border-radius: 8px;
        }

        .cart-notice {
            padding: 12px 15px;
            margin-bottom: 20px;
            background: #edf5e9;
            color: #31572c;
            border-radius: 5px;
        }

        .cart-error {
            padding: 12px 15px;
            margin-bottom: 20px;
            background: #f9e9e6;
            color: #8b3025;
            border-radius: 5px;
        }

        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            padding: 18px 5%;
            border-bottom: 1px solid #e5e0d8;
        }

        .cart-brand {
            font-size: 22px;
            font-weight: bold;
            color: #55402f;
            text-decoration: none;
        }

        .cart-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
        }

        .cart-nav a {
            color: #35291f;
            text-decoration: none;
        }

        .cart-footer {
            text-align: center;
            padding: 25px 15px;
            margin-top: 40px;
            border-top: 1px solid #e5e0d8;
        }

        @media (max-width: 760px) {
            .cart-layout {
                grid-template-columns: 1fr;
            }

            .cart-item {
                align-items: flex-start;
            }

            .cart-item img {
                width: 90px;
                height: 90px;
            }
        }
    </style>
</head>

<body>

<header class="cart-header">

    <a href="index.php" class="cart-brand">
        Afghan Antiques
    </a>

    <nav class="cart-nav">
        <a href="index.php">Home</a>
        <a href="products.php">Collection</a>
        <a href="cart.php">Cart (<?= (int)$cartCount ?>)</a>
        <a href="wishlist_page.php">My Wishlist</a>
    </nav>

</header>

<main class="cart-container">

    <p>YOUR COLLECTION</p>
    <h1>Shopping Cart</h1>

    <?php if ($message !== ''): ?>
        <div class="cart-notice">
            <?= escapeHtml($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="cart-error">
            <?= escapeHtml($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($databaseError): ?>

        <div class="cart-error">
            We could not retrieve your cart right now.
            Please refresh the page or try again later.
        </div>

    <?php elseif (empty($cartItems)): ?>

        <section class="cart-empty">

            <h2>Your cart is empty</h2>

            <p>
                Discover something special from our antique collection.
            </p>

            <a
                href="products.php"
                class="cart-primary-button"
                style="max-width:260px;margin:20px auto;"
            >
                Explore the Collection
            </a>

        </section>

    <?php else: ?>

        <div class="cart-layout">

            <section>

                <?php foreach ($cartItems as $item): ?>

                    <article class="cart-item">

                        <?php if (!empty($item['image'])): ?>

                            <img
                                src="<?= escapeHtml($item['image']) ?>"
                                alt="<?= escapeHtml($item['name']) ?>"
                                onerror="this.style.display='none';"
                            >

                        <?php else: ?>

                            <div class="image-placeholder">
                                Image unavailable
                            </div>

                        <?php endif; ?>

                        <div class="cart-item-details">

                            <h2>
                                <?= escapeHtml($item['name']) ?>
                            </h2>

                            <p>
                                Price: $<?= number_format($item['price'], 2) ?>
                            </p>

                            <p class="cart-item-price">
                                Total: $<?= number_format($item['total'], 2) ?>
                            </p>

                            <!-- Update quantity -->
                            <form method="POST" action="cart.php">

                                <input
                                    type="hidden"
                                    name="action"
                                    value="update"
                                >

                                <input
                                    type="hidden"
                                    name="product_id"
                                    value="<?= (int)$item['id'] ?>"
                                >

                                <input
                                    type="number"
                                    name="quantity"
                                    min="1"
                                    max="99"
                                    value="<?= (int)$item['quantity'] ?>"
                                    aria-label="Quantity"
                                    required
                                >

                                <button
                                    type="submit"
                                    class="cart-button cart-update"
                                >
                                    Update
                                </button>

                            </form>

                            <!-- Remove product -->
                            <form method="POST" action="cart.php">

                                <input
                                    type="hidden"
                                    name="action"
                                    value="remove"
                                >

                                <input
                                    type="hidden"
                                    name="product_id"
                                    value="<?= (int)$item['id'] ?>"
                                >

                                <button
                                    type="submit"
                                    class="cart-button cart-remove"
                                >
                                    Remove
                                </button>

                            </form>

                        </div>

                    </article>

                <?php endforeach; ?>

                <a
                    href="products.php"
                    class="cart-secondary-button"
                >
                    &larr; Continue Shopping
                </a>

            </section>

            <!-- Order Summary -->
            <aside class="cart-summary">

                <h2>Order Summary</h2>

                <div class="cart-summary-row">

                    <span>Items (<?= (int)$cartCount ?>)</span>

                    <span>
                        $<?= number_format($subtotal, 2) ?>
                    </span>

                </div>

                <div class="cart-summary-row">

                    <strong>Subtotal</strong>

                    <strong>
                        $<?= number_format($subtotal, 2) ?>
                    </strong>

                </div>

                <p>
                    Shipping and any applicable taxes will be calculated
                    during checkout.
                </p>

                <a
                    href="<?= escapeHtml($checkoutUrl) ?>"
                    class="cart-primary-button"
                >
                    Proceed to Checkout &rarr;
                </a>

            </aside>

        </div>

    <?php endif; ?>

</main>

<footer class="cart-footer">

    <p>
        &copy; <?= date('Y') ?> Afghan Antiques. All rights reserved.
    </p>

</footer>

</body>
</html>
