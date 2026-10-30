<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Admin-only access
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'admin'
) {
    http_response_code(403);
    exit('Access denied. Please log in as an administrator.');
}

// CSRF protection for delete actions
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch inventory
$stmt = $pdo->query("
    SELECT
        p.product_id,
        p.product_name,
        p.description,
        p.price,
        p.quantity,
        p.image,
        p.is_active,
        p.created_at,
        c.category_name
    FROM products p
    LEFT JOIN categories c
        ON p.category_id = c.category_id
    ORDER BY p.created_at DESC
");

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Safe HTML output
function h($value): string
{
    return htmlspecialchars(
        (string)($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

// Inventory statistics
$totalProducts = count($products);
$totalStock = 0;
$activeProducts = 0;

foreach ($products as $product) {
    $totalStock += (int)$product['quantity'];

    if ((int)$product['is_active'] === 1) {
        $activeProducts++;
    }
}

// Status messages
$added = isset($_GET['added']) && $_GET['added'] === '1';
$updated = isset($_GET['updated']) && $_GET['updated'] === '1';
$deleted = isset($_GET['deleted']) && $_GET['deleted'] === '1';
$statusUpdated = isset($_GET['status_updated'])
    && $_GET['status_updated'] === '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inventory Management | Afghan Antique Store</title>

    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --cream: #f7f3eb;
            --white: #fff;
            --brown: #382719;
            --brown-light: #60442e;
            --gold: #b89558;
            --text: #332a22;
            --muted: #81776b;
            --border: #e5ddd0;
            --green: #28643b;
            --red: #a33b2d;
        }

        body {
            margin: 0;
            background: var(--cream);
            color: var(--text);
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
        }

        .container {
            width: min(1250px, calc(100% - 40px));
            margin: auto;
            padding: 35px 0 55px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 18px;
            margin-bottom: 28px;
        }

        .eyebrow {
            margin-bottom: 5px;
            color: var(--gold);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 2px;
        }

        h1 {
            margin: 0;
            color: var(--brown);
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(28px, 4vw, 37px);
            font-weight: 600;
        }

        .subtitle {
            margin-top: 8px;
            color: var(--muted);
        }

        .btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 7px;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 7px;
            font: inherit;
            font-size: 12px;
            font-weight: 650;
            text-decoration: none;
            white-space: nowrap;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .btn-primary {
            background: var(--brown);
            border-color: var(--brown);
            color: white;
        }

        .btn-primary:hover {
            background: var(--brown-light);
            border-color: var(--gold);
        }

        .btn-secondary {
            background: white;
            color: var(--brown);
        }

        .btn-secondary:hover {
            border-color: var(--gold);
            background: #fcf8f0;
        }

        .btn-danger {
            background: #fff0ed;
            border-color: #f0d0ca;
            color: var(--red);
        }

        .btn-danger:hover {
            background: #fbe0db;
            border-color: var(--red);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 25px;
        }

        .stat-card {
            padding: 22px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 11px;
        }

        .stat-label {
            color: var(--muted);
            font-size: 13px;
        }

        .stat-value {
            display: block;
            margin-top: 5px;
            color: var(--brown);
            font-family: Georgia, "Times New Roman", serif;
            font-size: 30px;
            font-weight: 600;
        }

        .panel {
            overflow: hidden;
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(56, 39, 25, 0.04);
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            padding: 21px 24px;
            border-bottom: 1px solid var(--border);
        }

        .panel-header h2 {
            margin: 0;
            color: var(--brown);
            font-family: Georgia, "Times New Roman", serif;
            font-size: 22px;
            font-weight: 600;
        }

        .search {
            width: min(300px, 100%);
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 7px;
            font: inherit;
        }

        .search:focus {
            outline: 2px solid #e9dcc3;
            border-color: var(--gold);
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            padding: 14px 15px;
            background: #faf7f1;
            color: var(--muted);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.7px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        td {
            padding: 15px;
            border-top: 1px solid #f0ebe3;
            vertical-align: middle;
        }

        tbody tr:hover {
            background: #fdfbf7;
        }

        .product-cell {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 215px;
        }

        .product-image {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 57px;
            height: 57px;
            flex-shrink: 0;
            overflow: hidden;
            background: #f5f0e7;
            border: 1px solid var(--border);
            border-radius: 7px;
            color: var(--muted);
            font-size: 10px;
            text-align: center;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-name {
            color: var(--brown);
            font-weight: 700;
        }

        .product-id {
            margin-top: 3px;
            color: var(--muted);
            font-size: 12px;
        }

        .price {
            font-weight: 700;
            white-space: nowrap;
        }

        .stock {
            white-space: nowrap;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-active {
            background: #edf7ed;
            color: var(--green);
        }

        .status-inactive {
            background: #fff0ed;
            color: var(--red);
        }

        .actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 7px;
        }

        .actions form {
            margin: 0;
        }

        .notice {
            margin-bottom: 20px;
            padding: 13px 16px;
            background: #edf7ed;
            border-left: 4px solid var(--green);
            border-radius: 6px;
            color: var(--green);
        }

        .empty {
            padding: 55px 20px;
            color: var(--muted);
            text-align: center;
        }

        .empty h3 {
            margin: 0 0 8px;
            color: var(--brown);
            font-family: Georgia, "Times New Roman", serif;
            font-size: 23px;
        }

        .empty p {
            margin: 0 0 20px;
        }

        .no-results {
            display: none;
            padding: 24px;
            color: var(--muted);
            text-align: center;
        }

        @media (max-width: 850px) {
            .stats {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .stat-card {
                padding: 17px;
            }

            .stat-value {
                font-size: 25px;
            }
        }

        @media (max-width: 600px) {
            .container {
                width: calc(100% - 24px);
                padding-top: 23px;
            }

            .header .btn {
                width: 100%;
            }

            .panel-header {
                padding: 17px;
            }

            .search {
                width: 100%;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <header class="header">
        <div>
            <div class="eyebrow">AFGHAN ANTIQUE STORE</div>
            <h1>Inventory Management</h1>
            <p class="subtitle">
                Manage your antique collection and stock levels.
            </p>
        </div>

        <a href="add_product.php" class="btn btn-primary">
            + Add New Product
        </a>
    </header>

    <?php if ($added): ?>
        <div class="notice">
            Product added successfully to your inventory.
        </div>
    <?php endif; ?>

    <?php if ($updated): ?>
        <div class="notice">
            Product details updated successfully.
        </div>
    <?php endif; ?>

    <?php if ($deleted): ?>
        <div class="notice">
            Product removed from active inventory successfully.
        </div>
    <?php endif; ?>

    <?php if ($statusUpdated): ?>
        <div class="notice">
            Product status updated successfully.
        </div>
    <?php endif; ?>

    <section class="stats">
        <div class="stat-card">
            <span class="stat-label">Total Products</span>
            <span class="stat-value"><?= $totalProducts ?></span>
        </div>

        <div class="stat-card">
            <span class="stat-label">Active Products</span>
            <span class="stat-value"><?= $activeProducts ?></span>
        </div>

        <div class="stat-card">
            <span class="stat-label">Total Units in Stock</span>
            <span class="stat-value"><?= $totalStock ?></span>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Product Inventory</h2>

            <input
                type="search"
                id="productSearch"
                class="search"
                placeholder="Search products..."
                aria-label="Search inventory"
            >
        </div>

        <?php if (!$products): ?>
            <div class="empty">
                <h3>Your inventory is empty</h3>
                <p>Add your first antique product to get started.</p>

                <a href="add_product.php" class="btn btn-primary">
                    Add Your First Product
                </a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>

                    <tbody id="inventoryBody">
                    <?php foreach ($products as $product): ?>
                        <tr class="product-row">
                            <td>
                                <div class="product-cell">
                                    <div class="product-image">
                                        <?php if (!empty($product['image'])): ?>
                                            <img
                                                src="../<?= h($product['image']) ?>"
                                                alt="<?= h($product['product_name']) ?>"
                                                loading="lazy"
                                            >
                                        <?php else: ?>
                                            No image
                                        <?php endif; ?>
                                    </div>

                                    <div>
                                        <div class="product-name">
                                            <?= h($product['product_name']) ?>
                                        </div>

                                        <div class="product-id">
                                            ID: <?= (int)$product['product_id'] ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <?= h($product['category_name'] ?? 'Uncategorized') ?>
                            </td>

                            <td class="price">
                                $<?= number_format((float)$product['price'], 2) ?>
                            </td>

                            <td class="stock">
                                <?= (int)$product['quantity'] ?> units
                            </td>

                            <td>
                                <?php if ((int)$product['is_active'] === 1): ?>
                                    <span class="status status-active">
                                        Active
                                    </span>
                                <?php else: ?>
                                    <span class="status status-inactive">
                                        Deleted
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="actions">
                                    <a
                                        class="btn btn-secondary"
                                        href="edit_product.php?id=<?= (int)$product['product_id'] ?>"
                                    >
                                        Edit
                                    </a>

                                    <?php if ((int)$product['is_active'] === 1): ?>
                                        <form
                                            method="POST"
                                            action="delete_product.php"
                                            onsubmit="return confirm('Remove this product from active inventory? Its record and order history will be preserved.');"
                                        >
                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= h($_SESSION['csrf_token']) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="product_id"
                                                value="<?= (int)$product['product_id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-danger"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="status status-inactive">
                                            Removed
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="no-results" id="noResults">
                    No matching products found.
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>

<script>
    const searchInput = document.getElementById('productSearch');
    const rows = document.querySelectorAll('.product-row');
    const noResults = document.getElementById('noResults');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            let visibleCount = 0;

            rows.forEach(function (row) {
                const matches = row.textContent
                    .toLowerCase()
                    .includes(query);

                row.style.display = matches ? '' : 'none';

                if (matches) {
                    visibleCount++;
                }
            });

            if (noResults) {
                noResults.style.display =
                    visibleCount === 0 ? 'block' : 'none';
            }
        });
    }
</script>

</body>
</html>
