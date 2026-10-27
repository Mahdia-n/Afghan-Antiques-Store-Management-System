<?php
session_start();

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add") {
        $product_id = filter_input(
            INPUT_POST,
            "product_id",
            FILTER_VALIDATE_INT
        );

        $quantity = filter_input(
            INPUT_POST,
            "quantity",
            FILTER_VALIDATE_INT
        );

        if ($product_id && $product_id > 0 &&
            $quantity && $quantity > 0 && $quantity <= 99) {

            $_SESSION["cart"][$product_id] =
                ($_SESSION["cart"][$product_id] ?? 0) + $quantity;

            $message = "Product added to cart.";
        } else {
            $message = "Please enter a valid product and quantity.";
        }
    }

    if ($action === "update") {
        $product_id = filter_input(
            INPUT_POST,
            "product_id",
            FILTER_VALIDATE_INT
        );

        $quantity = filter_input(
            INPUT_POST,
            "quantity",
            FILTER_VALIDATE_INT
        );

        if ($product_id && isset($_SESSION["cart"][$product_id])) {
            if ($quantity && $quantity > 0 && $quantity <= 99) {
                $_SESSION["cart"][$product_id] = $quantity;
                $message = "Cart updated.";
            } else {
                unset($_SESSION["cart"][$product_id]);
                $message = "Product removed from cart.";
            }
        }
    }

    if ($action === "remove") {
        $product_id = filter_input(
            INPUT_POST,
            "product_id",
            FILTER_VALIDATE_INT
        );

        if ($product_id) {
            unset($_SESSION["cart"][$product_id]);
            $message = "Product removed from cart.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
</head>
<body>
    <h1>Shopping Cart</h1>

    <p><?= htmlspecialchars($message) ?></p>

    <?php if (empty($_SESSION["cart"])): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <?php foreach ($_SESSION["cart"] as $product_id => $quantity): ?>
            <div>
                <p>Product ID: <?= (int)$product_id ?></p>

                <form method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="product_id"
                           value="<?= (int)$product_id ?>">

                    <label>
                        Quantity:
                        <input type="number" name="quantity"
                               min="1" max="99"
                               value="<?= (int)$quantity ?>" required>
                    </label>

                    <button type="submit">Update</button>
                </form>

                <form method="POST">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="product_id"
                           value="<?= (int)$product_id ?>">
                    <button type="submit">Remove</button>
                </form>
            </div>
            <hr>
        <?php endforeach; ?>
    <?php endif; ?>

    <p><a href="products.php">Continue Shopping</a></p>
    <a href="checkout.php" class="btn">Checkout</a>
</body>
</html>