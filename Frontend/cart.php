<?php
session_start();

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

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$message = "";

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

    if ($action === 'update' && $id && isset($products[$id])) {
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

        if ($quantity !== false && $quantity !== null && $quantity > 0 && $quantity <= 99) {
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id] = $quantity;
            }
        } else {
            unset($_SESSION['cart'][$id]);
        }
    } elseif ($action === 'remove' && $id) {
        unset($_SESSION['cart'][$id]);
    } elseif ($action === 'add' && $id && isset($products[$id])) {
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
        $quantity = ($quantity && $quantity > 0 && $quantity <= 99) ? $quantity : 1;

        $_SESSION['cart'][$id] = min(
            99,
            ($_SESSION['cart'][$id] ?? 0) + $quantity
        );
        $message = $products[$id]['name'] . " added to your cart.";
    }

    if ($action !== 'add') {
        header('Location: cart.php');
        exit;
    }
}

$cartItems = [];
$subtotal = 0;

foreach ($_SESSION['cart'] as $id => $quantity) {
    if (isset($products[$id])) {
        $itemTotal = $products[$id]['price'] * $quantity;
        $subtotal += $itemTotal;

        $cartItems[] = [
            'id' => $id,
            'name' => $products[$id]['name'],
            'price' => $products[$id]['price'],
            'image' => $products[$id]['image'],
            'quantity' => $quantity,
            'total' => $itemTotal
        ];
    }
}

function escapeHtml($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | Afghan Antiques</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="cart-page">

<header class="cart-header">
    <a href="index.php" class="cart-brand">Afghan Antiques</a>

    <nav class="cart-nav">
        <a href="index.php">Home</a>
        <a href="products.php">Collection</a>
        <a href="cart.php" class="active">Cart (<?= count($_SESSION['cart']) ?>)</a>
    </nav>
</header>

<main class="cart-container">
    <div class="cart-heading">
        <p class="cart-eyebrow">YOUR SELECTION</p>
        <h1>Shopping Cart</h1>
        <p>Review your selected antiques before checkout.</p>
    </div>

    <?php if ($message !== ''): ?>
        <div class="cart-notice">
            <?= escapeHtml($message) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>
        <section class="empty-cart">
            <div class="empty-cart-icon">🛍</div>
            <h2>Your cart is waiting</h2>
            <p>You haven't added any antiques to your cart yet.</p>
            <a href="products.php" class="cart-primary-button">
                Explore the Collection
            </a>
        </section>
    <?php else: ?>
        <div class="cart-layout">
            <section class="cart-items">
                <div class="cart-items-heading">
                    <h2>Your Items</h2>
                    <span><?= count($cartItems) ?> product(s)</span>
                </div>

                <?php foreach ($cartItems as $item): ?>
                    <article class="cart-item">
                        <a href="product.php?id=<?= (int)$item['id'] ?>"
                           class="cart-item-image">
                            <img
                                src="<?= escapeHtml($item['image']) ?>"
                                alt="<?= escapeHtml($item['name']) ?>"
                            >
                        </a>

                        <div class="cart-item-info">
                            <p class="cart-item-category">AUTHENTIC FINDS</p>
                            <h3>
                                <a href="product.php?id=<?= (int)$item['id'] ?>">
                                    <?= escapeHtml($item['name']) ?>
                                </a>
                            </h3>
                            <p class="cart-item-price">
                                $<?= number_format($item['price'], 2) ?> each
                            </p>

                            <div class="cart-item-actions">
                                <form method="POST" action="cart.php"
                                      class="quantity-form">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id"
                                           value="<?= (int)$item['id'] ?>">

                                    <label for="quantity-<?= (int)$item['id'] ?>">
                                        Qty
                                    </label>
                                    <input
                                        id="quantity-<?= (int)$item['id'] ?>"
                                        type="number"
                                        name="quantity"
                                        min="1"
                                        max="99"
                                        value="<?= (int)$item['quantity'] ?>"
                                        required
                                    >
                                    <button type="submit" class="cart-update-button">
                                        Update
                                    </button>
                                </form>

                                <form method="POST" action="cart.php">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id"
                                           value="<?= (int)$item['id'] ?>">
                                    <button type="submit" class="cart-remove-button">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="cart-item-total">
                            $<?= number_format($item['total'], 2) ?>
                        </div>
                    </article>
                <?php endforeach; ?>

                <a href="products.php" class="continue-shopping">
                    ← Continue Shopping
                </a>
            </section>

            <aside class="cart-summary">
                <h2>Order Summary</h2>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>$<?= number_format($subtotal, 2) ?></span>
                </div>

                <div class="summary-row">
                    <span>Shipping</span>
                    <span>Calculated at checkout</span>
                </div>

                <div class="summary-row summary-total">
                    <span>Estimated Total</span>
                    <span>$<?= number_format($subtotal, 2) ?></span>
                </div>

                <p class="cart-summary-note">
                    Shipping and any applicable taxes will be confirmed
                    before payment.
                </p>

                <?php
                $checkoutUrl = isset($_SESSION['user_id'])
                ? 'checkout.php'
                : 'register.php?redirect=checkout.php';
                ?>

<a href="<?= escapeHtml($checkoutUrl) ?>"
   class="cart-primary-button cart-checkout-button">
    Proceed to Checkout →
</a>

                <div class="cart-security">
                    <span>✓</span>
                    Secure checkout
                </div>
            </aside>
        </div>
    <?php endif; ?>
</main>

<footer class="cart-footer">
    <p>© <?= date('Y') ?> Afghan Antiques. All rights reserved.</p>
</footer>

</body>
</html>
