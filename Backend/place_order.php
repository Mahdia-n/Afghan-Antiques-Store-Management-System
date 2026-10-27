<?php
session_start();

if (!isset($_SESSION["customer_id"])) {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION["cart"])) {
    header("Location: cart.php");
    exit;
}

$message = "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
</head>
<body>
    <h1>Checkout</h1>

    <p>Please enter your delivery address.</p>

    <form action="place_order.php" method="POST">
        <label for="address">Delivery Address</label><br>
        <textarea id="address" name="delivery_address"
                  rows="4" required></textarea>
        <br><br>

        <button type="submit">Place Order</button>
    </form>

    <p><a href="cart.php">Back to Cart</a></p>
</body>
</html>