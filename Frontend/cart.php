<?php
session_start();

require_once __DIR__ . '/config/database.php';

function escapeHtml($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Current products used by the cart.
// These IDs must match the corresponding products in your database.
$products = [
    1 => [
        "name" => "Afghan Antique Mantel Clock",
        "price" => 185.00,
        "image" => "assets/images/antique-mantel-clock.png"
    ],
    2 => [
        "name" => "Traditional Ceramic Vase",
        "price" => 95.00,
        "image" => "assets/images/ceramic-vase.png"
    ],
    3 => [
        "name" => "Vintage Wooden Cabinet",
        "price" => 450.00,
        "image" => "assets/images/vintage-wooden-cabinet.png"
    ],
    4 => [
        "name" => "Victorian Table Lamp",
        "price" => 125.00,
        "image" => "assets/images/victorian-table-lamp.png"
    ]
];

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$message = '';
$error = '';

// Handle adding, updating, and removing cart items.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

    if ($action === 'add') {
        $quantity = filter_input(
            INPUT_POST,
            'quantity',
            FILTER_VALIDATE_INT
        );

        if (!$id || !isset($products[$id])) {
            $error = 'This product could not be found in the cart.';
        } else {
            $quantity = ($quantity && $quantity > 0)
                ? min($quantity, 99)
                : 1;

            $currentQuantity = (int)($_SESSION['cart'][$id] ?? 0);

            $_SESSION['cart'][$id] = min(
                99,
                $currentQuantity + $quantity
            );

            $message = $products[$id]['name'] . ' added to your cart.';
        }
    } elseif ($action === 'update') {
        $quantity = filter_input(
            INPUT_POST,
            'quantity',
            FILTER_VALIDATE_INT
        );

        if ($id && isset($products[$id])) {
            if ($quantity !== false && $quantity !== null
                && $quantity >= 1 && $quantity <= 99) {
                $_SESSION['cart'][$id] = $quantity;
                $message = 'Cart quantity updated.';
            } else {
                $error = 'Please enter a quantity between 1 and 99.';
            }
        }
    } elseif ($action === 'remove') {
        if ($id) {
            unset($_SESSION['cart'][$id]);
            $message = 'Item removed from your cart.';
        }
    }

    // Redirect after update/remove to prevent repeated submissions.
    if ($action === 'update' || $action === 'remove') {
        header('Location: cart.php');
        exit;
    }
}

// Build the cart and calculate the subtotal.
$cartItems = [];
$subtotal = 0;

foreach ($_SESSION['cart'] as $id => $quantity) {
    $id = (int)$id;
    $quantity = (int)$quantity;

    if (isset($products[$id]) && $quantity > 0) {
        $product = $products[$id];
        $itemTotal = $product['price'] * $quantity;

        $cartItems[] = [
            'id' => $id,
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => $product['image'],
            'quantity' => $quantity,
            'total' => $itemTotal
        ];

        $subtotal += $itemTotal;
    }
}

$cartCount = array_sum($_SESSION['cart']);

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
    <a href="index.php" class="cart-brand">Afghan Antiques</a>

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

    <?php if (empty($cartItems)): ?>

        <section class="cart-empty">
            <h2>Your cart is empty</h2>
            <p>Discover something special from our antique collection.</p>

            <a href="products.php" class="cart-primary-button"
               style="max-width:260px;margin:20px auto;">
                Explore the Collection
            </a>
        </section>

    <?php else: ?>

        <div class="cart-layout">

            <section>
                <?php foreach ($cartItems as $item): ?>
                    <article class="cart-item">

                        <img
                            src="<?= escapeHtml($item['image']) ?>"
                            alt="<?= escapeHtml($item['name']) ?>"
                        >

                        <div class="cart-item-details">
                            <h2><?= escapeHtml($item['name']) ?></h2>

                            <p>
                                Price: $<?= number_format($item['price'], 2) ?>
                            </p>

                            <p class="cart-item-price">
                                Total: $<?= number_format($item['total'], 2) ?>
                            </p>

                            <form method="POST" action="cart.php">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id"
                                       value="<?= (int)$item['id'] ?>">

                                <input
                                    type="number"
                                    name="quantity"
                                    min="1"
                                    max="99"
                                    value="<?= (int)$item['quantity'] ?>"
                                    aria-label="Quantity"
                                    required
                                >

                                <button type="submit"
                                        class="cart-button cart-update">
                                    Update
                                </button>
                            </form>

                            <form method="POST" action="cart.php">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id"
                                       value="<?= (int)$item['id'] ?>">

                                <button type="submit"
                                        class="cart-button cart-remove">
                                    Remove
                                </button>
                            </form>
                        </div>

                    </article>
                <?php endforeach; ?>

                <a href="products.php" class="cart-secondary-button">
                    ← Continue Shopping
                </a>
            </section>

            <aside class="cart-summary">
                <h2>Order Summary</h2>

                <div class="cart-summary-row">
                    <span>Items (<?= (int)$cartCount ?>)</span>
                    <span>$<?= number_format($subtotal, 2) ?></span>
                </div>

                <div class="cart-summary-row">
                    <strong>Subtotal</strong>
                    <strong>$<?= number_format($subtotal, 2) ?></strong>
                </div>

                <p>Shipping and any applicable taxes will be calculated during checkout.</p>

                <a href="<?= escapeHtml($checkoutUrl) ?>"
                   class="cart-primary-button">
                    Proceed to Checkout →
                </a>
            </aside>

        </div>

    <?php endif; ?>

</main>

<footer class="cart-footer">
    <p>© <?= date('Y') ?> Afghan Antiques. All rights reserved.</p>
</footer>

</body>
</html>
