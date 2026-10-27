<?php
session_start();
require_once "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
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

    if (!$product_id || !$quantity || $quantity < 1 || $quantity > 99) {
        $message = "Please select a valid quantity.";
    } else {
        try {
            $stmt = $pdo->prepare(
                "SELECT id, stock FROM Products WHERE id = ?"
            );
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                $message = "Product not found.";
            } elseif ($quantity > (int)$product["stock"]) {
                $message = "Not enough stock available.";
            } else {
                if (!isset($_SESSION["cart"])) {
                    $_SESSION["cart"] = [];
                }

                $current = (int)($_SESSION["cart"][$product_id] ?? 0);

                if ($current + $quantity > (int)$product["stock"]) {
                    $message = "Your cart quantity exceeds available stock.";
                } else {
                    $_SESSION["cart"][$product_id] = $current + $quantity;
                    $message = "Product added to cart successfully!";
                }
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $message = "Could not add product. Please try again.";
        }
    }
}

try {
    $stmt = $pdo->query(
        "SELECT id, name, description, price, image, stock
         FROM Products
         ORDER BY id DESC"
    );
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Could not load products. Please check the database.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Afghan Antique Store - Products</title>
</head>
<body>

    <h1>Afghan Antique Store</h1>
    <h2>Our Products</h2>

    <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>

    <p>
        <a href="cart.php">View Shopping Cart</a>
    </p>

    <?php if (empty($products)): ?>
        <p>No products available yet. Please add products to the database.</p>
    <?php else: ?>

        <?php foreach ($products as $product): ?>
            <div>
                <h3>
                    <?= htmlspecialchars($product["name"], ENT_QUOTES, 'UTF-8') ?>
                </h3>

                <p>
                    <?= htmlspecialchars(
                        $product["description"] ?? "",
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </p>

                <p>Price: <?= number_format((float)$product["price"], 2) ?></p>

                <p>Stock: <?= (int)$product["stock"] ?></p>

                <?php if (!empty($product["image"])): ?>
                    <img
                        src="<?= htmlspecialchars($product["image"], ENT_QUOTES, 'UTF-8') ?>"
                        alt="<?= htmlspecialchars($product["name"], ENT_QUOTES, 'UTF-8') ?>"
                        width="180"
                    >
                <?php endif; ?>

                <?php if ((int)$product["stock"] > 0): ?>
                    <form method="POST" action="">
                        <input
                            type="hidden"
                            name="product_id"
                            value="<?= (int)$product["id"] ?>"
                        >

                        <label for="quantity-<?= (int)$product["id"] ?>">
                            Quantity:
                        </label>

                        <input
                            type="number"
                            id="quantity-<?= (int)$product["id"] ?>"
                            name="quantity"
                            min="1"
                            max="<?= min(99, (int)$product["stock"]) ?>"
                            value="1"
                            required
                        >

                        <button type="submit">Add to Cart</button>
                    </form>
                <?php else: ?>
                    <p>Out of stock</p>
                <?php endif; ?>
            </div>

            <hr>
        <?php endforeach; ?>

    <?php endif; ?>

</body>
</html>