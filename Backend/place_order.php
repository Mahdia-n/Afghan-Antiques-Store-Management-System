<?php
session_start();
require_once "db.php";

// Check if the customer is logged in
if (!isset($_SESSION["customer_id"])) {
    header("Location: login.php");
    exit;
}

// Allow only POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: checkout.php");
    exit;
}

// Check if the cart is empty
if (empty($_SESSION["cart"])) {
    header("Location: cart.php");
    exit;
}

// Get delivery address and customer ID
$address = trim($_POST["delivery_address"] ?? "");
$user_id = (int) $_SESSION["customer_id"];
$cart = $_SESSION["cart"];

if ($address === "") {
    exit("Please enter your delivery address.");
}

try {
    $pdo->beginTransaction();

    $items = [];
    $total = 0;

    // Get product prices from the database
    $productStmt = $pdo->prepare(
        "SELECT id, price FROM Products WHERE id = ?"
    );

    foreach ($cart as $product_id => $quantity) {
        $product_id = (int) $product_id;
        $quantity = (int) $quantity;

        if ($product_id <= 0 || $quantity <= 0 || $quantity > 99) {
            throw new Exception("Invalid product or quantity.");
        }

        $productStmt->execute([$product_id]);
        $product = $productStmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new Exception(
                "Product not found. ID: " . $product_id
            );
        }

        $price = (float) $product["price"];

        $items[] = [
            "product_id" => $product_id,
            "quantity" => $quantity,
            "price" => $price
        ];

        $total += $price * $quantity;
    }

    // Save the order
    $orderStmt = $pdo->prepare(
        "INSERT INTO Orders
        (user_id, total_amount, status, delivery_address, created_at)
        VALUES (?, ?, ?, ?, NOW())"
    );

    $orderStmt->execute([
        $user_id,
        $total,
        "Pending",
        $address
    ]);

    $order_id = $pdo->lastInsertId();

    // Save order items
    $itemStmt = $pdo->prepare(
        "INSERT INTO order_items
        (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)"
    );

    foreach ($items as $item) {
        $itemStmt->execute([
            $order_id,
            $item["product_id"],
            $item["quantity"],
            $item["price"]
        ]);
    }

    // Complete the order
    $pdo->commit();

    // Empty the cart after successful order
    unset($_SESSION["cart"]);

    echo "<h2>Order placed successfully!</h2>";
    echo "<p>Order number: "
        . htmlspecialchars((string) $order_id) . "</p>";
    echo "<p>Total amount: "
        . htmlspecialchars(number_format($total, 2)) . "</p>";
    echo '<p><a href="products.php">Continue Shopping</a></p>';

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo "<h3>Order failed!</h3>";
    echo "<p>Database error: "
        . htmlspecialchars($e->getMessage()) . "</p>";
}
?>