<?php
session_start();
require_once "db.php";

// Check if customer is logged in
if (!isset($_SESSION["customer_id"])) {
    header("Location: login.php");
    exit;
}

$customer_id = (int) $_SESSION["customer_id"];

try {
    // Get orders belonging to this customer
    $stmt = $pdo->prepare(
        "SELECT id, total_amount, status, delivery_address, created_at
         FROM Orders
         WHERE user_id = ?
         ORDER BY created_at DESC"
    );

    $stmt->execute([$customer_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log($e->getMessage());
    exit("Could not load order history. Please check the database.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Order History</title>
</head>
<body>

    <h1>My Order History</h1>

    <?php if (empty($orders)): ?>

        <p>You have not placed any orders yet.</p>

    <?php else: ?>

        <?php foreach ($orders as $order): ?>

            <hr>

            <h3>Order #<?= (int) $order["id"] ?></h3>

            <p>
                <strong>Total:</strong>
                <?= htmlspecialchars(number_format((float) $order["total_amount"], 2)) ?>
            </p>

            <p>
                <strong>Status:</strong>
                <?= htmlspecialchars($order["status"]) ?>
            </p>

            <p>
                <strong>Delivery Address:</strong>
                <?= htmlspecialchars($order["delivery_address"]) ?>
            </p>

            <p>
                <strong>Order Date:</strong>
                <?= htmlspecialchars($order["created_at"]) ?>
            </p>

        <?php endforeach; ?>

    <?php endif; ?>

    <hr>

    <p><a href="products.php">Continue Shopping</a></p>
    <p><a href="cart.php">Shopping Cart</a></p>

</body>
</html>