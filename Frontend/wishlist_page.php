<?php
session_start();

require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

function escapeHtml($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$userId = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['remove_product_id'])) {

    $productId = filter_input(
        INPUT_POST,
        'remove_product_id',
        FILTER_VALIDATE_INT
    );

    if ($productId && $productId > 0) {
        $stmt = $pdo->prepare(
            'DELETE FROM wishlist
             WHERE user_id = ? AND product_id = ?'
        );
        $stmt->execute([$userId, $productId]);
    }

    header('Location: wishlist_page.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT p.product_id, p.product_name, p.description,
            p.price, p.image, p.quantity
     FROM wishlist w
     INNER JOIN products p ON p.product_id = w.product_id
     WHERE w.user_id = ? AND p.is_active = 1
     ORDER BY p.product_name'
);

$stmt->execute([$userId]);
$wishlistItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cartCount = isset($_SESSION['cart'])
    ? array_sum($_SESSION['cart'])
    : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Wishlist | Afghan Antiques</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .wishlist-container {
            max-width: 1150px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .wishlist-heading {
            margin-bottom: 30px;
        }

        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 25px;
        }

        .wishlist-card {
            border: 1px solid #e5e0d8;
            border-radius: 8px;
            padding: 18px;
            background: #fff;
        }

        .wishlist-card img {
            width: 100%;
            height: 220px;
            object-fit: contain;
            margin-bottom: 15px;
        }

        .wishlist-card h2 {
            font-size: 20px;
            margin: 8px 0;
        }

        .wishlist-price {
            font-size: 19px;
            font-weight: bold;
            margin: 12px 0;
        }

        .wishlist-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .wishlist-actions button {
            display: inline-block;
            padding: 10px 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .wishlist-add {
            background: #55402f;
            color: white;
        }

        .wishlist-remove {
            background: #eee8e1;
            color: #35291f;
        }

        .wishlist-empty {
            padding: 35px;
            text-align: center;
            background: #f8f5f0;
            border-radius: 8px;
        }

        .wishlist-notice {
            padding: 12px 15px;
            margin-bottom: 20px;
            background: #edf5e9;
            color: #31572c;
            border-radius: 5px;
        }

        .wishlist-actions form {
            margin: 0;
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
        <a href="wishlist_page.php" class="active">My Wishlist</a>
    </nav>
</header>

<main class="wishlist-container">

    <div class="wishlist-heading">
        <p>YOUR SAVED COLLECTION</p>
        <h1>My Wishlist</h1>
        <p>Keep track of the antiques you love and add them to your cart when you're ready.</p>
    </div>

    <?php if (isset($_GET['added'])): ?>
        <div class="wishlist-notice">
            Your wishlist has been updated.
        </div>
    <?php endif; ?>

    <?php if (empty($wishlistItems)): ?>

        <section class="wishlist-empty">
            <h2>Your wishlist is empty</h2>
            <p>Explore our collection and save your favorite antiques here.</p>
            <a href="products.php" class="cart-primary-button">
                Explore the Collection
            </a>
        </section>

    <?php else: ?>

        <div class="wishlist-grid">

            <?php foreach ($wishlistItems as $item): ?>
                <article class="wishlist-card">

                    <img
                        src="<?= escapeHtml($item['image']) ?>"
                        alt="<?= escapeHtml($item['product_name']) ?>"
                    >

                    <h2><?= escapeHtml($item['product_name']) ?></h2>

                    <p>
                        <?= escapeHtml($item['description'] ?? '') ?>
                    </p>

                    <p class="wishlist-price">
                        $<?= number_format((float)$item['price'], 2) ?>
                    </p>

                    <div class="wishlist-actions">

                        <?php if ((int)$item['quantity'] > 0): ?>
                            <form method="POST" action="cart.php">
                                <input type="hidden" name="action" value="add">

                                <input
                                    type="hidden"
                                    name="product_id"
                                    value="<?= (int)$item['product_id'] ?>"
                                >

                                <input type="hidden" name="quantity" value="1">

                                <button type="submit" class="wishlist-add">
                                    Add to Cart
                                </button>
                            </form>
                        <?php else: ?>
                            <span>Currently out of stock</span>
                        <?php endif; ?>

                        <form method="POST" action="wishlist_page.php">
                            <input
                                type="hidden"
                                name="remove_product_id"
                                value="<?= (int)$item['product_id'] ?>"
                            >

                            <button type="submit" class="wishlist-remove">
                                Remove
                            </button>
                        </form>

                    </div>
                </article>
            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</main>

<footer class="cart-footer">
    <p>© <?= date('Y') ?> Afghan Antiques. All rights reserved.</p>
</footer>

</body>
</html>
