<?php
session_start();

require_once __DIR__ . '/config/database.php';

function redirectWithError(string $message): void {
    $_SESSION['checkout_error'] = $message;
    header('Location: checkout.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=checkout.php');
    exit;
}

// Validate the checkout form's CSRF token.
$submittedToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';

if (
    !is_string($submittedToken) ||
    !is_string($sessionToken) ||
    $sessionToken === '' ||
    !hash_equals($sessionToken, $submittedToken)
) {
    redirectWithError('Your session expired. Please refresh checkout and try again.');
}

$cart = $_SESSION['cart'] ?? [];

if (!is_array($cart) || empty($cart)) {
    redirectWithError('Your cart is empty. Please add products before checking out.');
}

$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['shipping_address'] ?? '');
$city = trim($_POST['shipping_city'] ?? '');
$state = trim($_POST['shipping_state'] ?? '');
$postalCode = trim($_POST['shipping_postal_code'] ?? '');

if (
    $phone === '' ||
    $address === '' ||
    $city === '' ||
    $postalCode === ''
) {
    redirectWithError('Please complete all required delivery fields.');
}

if (
    strlen($phone) > 30 ||
    strlen($address) > 255 ||
    strlen($city) > 100 ||
    strlen($state) > 100 ||
    strlen($postalCode) > 20
) {
    redirectWithError('One or more delivery fields are too long.');
}

try {
    $pdo->beginTransaction();

    // Get the logged-in customer's information.
    $userStmt = $pdo->prepare(
        'SELECT user_id, full_name, email
         FROM users
         WHERE user_id = ?
         LIMIT 1'
    );
    $userStmt->execute([(int) $_SESSION['user_id']]);
    $user = $userStmt->fetch();

    if (!$user) {
        throw new RuntimeException('Your customer account could not be found.');
    }

    $orderProducts = [];
    $orderTotal = 0.00;

    // Lock inventory rows while checking stock and creating the order.
    foreach ($cart as $productId => $quantity) {
        $productId = filter_var($productId, FILTER_VALIDATE_INT);
        $quantity = filter_var(
            $quantity,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 99]]
        );

        if (!$productId || !$quantity) {
            throw new RuntimeException('Your cart contains an invalid item. Please review your cart.');
        }

        $productStmt = $pdo->prepare(
            'SELECT product_id, product_name, price, quantity
             FROM products
             WHERE product_id = ? AND is_active = 1
             FOR UPDATE'
        );
        $productStmt->execute([$productId]);
        $product = $productStmt->fetch();

        if (!$product) {
            throw new RuntimeException('A product in your cart is no longer available.');
        }

        if ((int) $product['quantity'] < $quantity) {
            throw new RuntimeException(
                'Insufficient stock for ' . $product['product_name'] .
                '. Please update your cart.'
            );
        }

        $unitPrice = (float) $product['price'];
        $lineTotal = round($unitPrice * $quantity, 2);
        $orderTotal += $lineTotal;

        $orderProducts[] = [
            'product_id' => (int) $product['product_id'],
            'product_name' => $product['product_name'],
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'line_total' => $lineTotal
        ];
    }

    $orderTotal = round($orderTotal, 2);

    // Save the order.
    $orderStmt = $pdo->prepare(
        "INSERT INTO orders (
            user_id, full_name, email, phone,
            shipping_address, shipping_city, shipping_state,
            shipping_postal_code, order_total, order_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
    );

    $orderStmt->execute([
        (int) $user['user_id'],
        $user['full_name'],
        $user['email'],
        $phone,
        $address,
        $city,
        $state !== '' ? $state : null,
        $postalCode,
        $orderTotal
    ]);

    $orderId = (int) $pdo->lastInsertId();

    // Save order items and update inventory.
    $itemStmt = $pdo->prepare(
        'INSERT INTO order_items (
            order_id, product_id, product_name,
            unit_price, quantity, line_total
        ) VALUES (?, ?, ?, ?, ?, ?)'
    );

    $stockStmt = $pdo->prepare(
        'UPDATE products
         SET quantity = quantity - ?
         WHERE product_id = ? AND quantity >= ?'
    );

    foreach ($orderProducts as $item) {
        $itemStmt->execute([
            $orderId,
            $item['product_id'],
            $item['product_name'],
            $item['unit_price'],
            $item['quantity'],
            $item['line_total']
        ]);

        $stockStmt->execute([
            $item['quantity'],
            $item['product_id'],
            $item['quantity']
        ]);

        if ($stockStmt->rowCount() !== 1) {
            throw new RuntimeException(
                'Inventory changed while processing your order. Please try again.'
            );
        }
    }

    // Commit only when the order and all its items are saved.
    $pdo->commit();

    $_SESSION['last_order_id'] = $orderId;
    $_SESSION['last_order_total'] = $orderTotal;
    $_SESSION['cart'] = [];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    header('Location: order_success.php');
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Order placement error: ' . $e->getMessage());

    if ($e instanceof RuntimeException) {
        redirectWithError($e->getMessage());
    }

    redirectWithError(
        'We could not place your order. Your cart has been preserved. Please try again.'
    );
}
