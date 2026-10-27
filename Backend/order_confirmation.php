<?php
session_start();

if (!isset($_SESSION["customer_id"])) {
    header("Location: login.php");
    exit;
}

$orderId = $_SESSION["last_order_id"] ?? null;

if (!$orderId) {
    header("Location: products.php");
    exit;
}

unset($_SESSION["last_order_id"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
</head>
<body>
    <h1>Order Placed Successfully!</h1>

    <p>Thank you for your purchase.</p>

    <p>
        Your order number is:
        <strong><?= htmlspecialchars((string)$orderId) ?></strong>
    </p>

    <p>Your order status is Pending.</p>

    <a href="products.php">Continue Shopping</a>
    <br><br>
    <a href="dashboard.php">My Dashboard</a>
</body>
</html>