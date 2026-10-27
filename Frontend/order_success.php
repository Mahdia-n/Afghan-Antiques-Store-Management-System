<?php
session_start();

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['last_order_id']) ||
    !isset($_SESSION['last_order_total'])
) {
    header('Location: index.php');
    exit;
}

$orderId = (int) $_SESSION['last_order_id'];
$orderTotal = (float) $_SESSION['last_order_total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Received | Afghan Antiques</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">

<header class="auth-header">
    <a href="index.php" class="auth-brand">Afghan Antiques</a>
    <a href="products.php" class="auth-back">Browse Collection</a>
</header>

<main class="auth-container">
    <section class="auth-card">
        <p class="auth-eyebrow">THANK YOU FOR YOUR ORDER</p>
        <h1>Order received!</h1>

        <p class="auth-subtitle">
            Thank you for shopping with Afghan Antiques.
            Your order has been saved successfully.
        </p>

        <div class="auth-message" role="status">
            <p>
                <strong>Order number:</strong>
                #<?= htmlspecialchars((string) $orderId, ENT_QUOTES, 'UTF-8') ?>
            </p>
            <p>
                <strong>Order total:</strong>
                $<?= number_format($orderTotal, 2) ?>
            </p>
            <p>THANK YOU FOR YOUR SHOPPING!</p>
        </div>

        <a href="products.php" class="auth-submit"
           style="display:block;text-align:center;text-decoration:none;">
            Continue Shopping
        </a>
    </section>
</main>

</body>
</html>