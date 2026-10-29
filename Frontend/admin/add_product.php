<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Only logged-in administrators can add products.
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'admin'
) {
    http_response_code(403);
    exit('Access denied. Please log in with an administrator account.');
}

$message = '';
$error = '';

// Load product categories.
try {
    $stmt = $pdo->query(
        "SELECT category_id, category_name
         FROM categories
         ORDER BY category_name"
    );
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
    $error = 'Could not load product categories.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $name = trim($_POST['product_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priceInput = trim($_POST['price'] ?? '');
    $quantityInput = trim($_POST['quantity'] ?? '');
    $categoryInput = $_POST['category_id'] ?? '';

    if ($name === '') {
        $error = 'Please enter a product name.';
    } elseif (
        $priceInput === '' ||
        !is_numeric($priceInput) ||
        (float)$priceInput < 0
    ) {
        $error = 'Enter a valid, non-negative price.';
    } elseif (
        $quantityInput === '' ||
        filter_var($quantityInput, FILTER_VALIDATE_INT) === false ||
        (int)$quantityInput < 0
    ) {
        $error = 'Enter a valid, non-negative quantity.';
    }

    // Validate the selected category against the database.
    $categoryId = null;

    if (!$error && $categoryInput !== '') {
        $categoryId = filter_var(
            $categoryInput,
            FILTER_VALIDATE_INT
        );

        if ($categoryId === false) {
            $error = 'Please select a valid category.';
        } else {
            $check = $pdo->prepare(
                "SELECT COUNT(*) FROM categories WHERE category_id = ?"
            );
            $check->execute([$categoryId]);

            if (!$check->fetchColumn()) {
                $error = 'The selected category does not exist.';
            }
        }
    }

    // Handle optional image upload.
    $imagePath = null;

    if (!$error && isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $error = 'The image upload failed.';
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $error = 'The image must be smaller than 5 MB.';
        } else {
            $tmp = $_FILES['image']['tmp_name'];
            $imageInfo = @getimagesize($tmp);

            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'image/gif'  => 'gif'
            ];

            $mime = $imageInfo['mime'] ?? '';

            if (!$imageInfo || !isset($allowedTypes[$mime])) {
                $error = 'Upload a valid JPG, PNG, WEBP, or GIF image.';
            } else {
                $imageDirectory = __DIR__ . '/../assets/images/';

                if (!is_dir($imageDirectory) &&
                    !mkdir($imageDirectory, 0755, true)) {
                    $error = 'Could not create the image directory.';
                } elseif (!is_writable($imageDirectory)) {
                    $error = 'The image directory is not writable.';
                } else {
                    $filename = bin2hex(random_bytes(16))
                        . '.' . $allowedTypes[$mime];

                    if (move_uploaded_file(
                        $tmp,
                        $imageDirectory . $filename
                    )) {
                        $imagePath = 'assets/images/' . $filename;
                    } else {
                        $error = 'Could not save the uploaded image.';
                    }
                }
            }
        }
    }

    // Save the product.
    if (!$error) {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO products
                    (category_id, product_name, description,
                     price, quantity, image, is_active)
                 VALUES
                    (:category_id, :product_name, :description,
                     :price, :quantity, :image, 1)"
            );

            $stmt->execute([
                ':category_id' => $categoryId,
                ':product_name' => $name,
                ':description' => $description,
                ':price' => number_format((float)$priceInput, 2, '.', ''),
                ':quantity' => (int)$quantityInput,
                ':image' => $imagePath
            ]);

            header('Location: products.php?added=1');
            exit;
        } catch (PDOException $e) {
            if ($imagePath !== null) {
                $savedImage = __DIR__ . '/../' . $imagePath;
                if (is_file($savedImage)) {
                    unlink($savedImage);
                }
            }

            $error = 'Could not add the product. Please check the database configuration.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product | Afghan Antique Store</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 30px 16px;
            background: #f5f1e9;
            color: #30271f;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 720px;
            margin: 0 auto;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        h1 { margin: 0; font-size: 27px; }

        .panel {
            background: #fff;
            border-radius: 12px;
            padding: 28px;
            box-shadow: 0 4px 18px #0000000d;
        }

        label {
            display: block;
            margin: 18px 0 7px;
            font-weight: bold;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d7cfc3;
            border-radius: 6px;
            font: inherit;
            background: white;
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        .hint {
            display: block;
            margin-top: 6px;
            color: #71685f;
            font-size: 13px;
        }

        button, .link {
            display: inline-block;
            border: 0;
            border-radius: 6px;
            padding: 12px 17px;
            font-size: 15px;
            text-decoration: none;
            cursor: pointer;
        }

        button {
            margin-top: 24px;
            background: #6b4328;
            color: white;
        }

        .link { background: #e9e1d5; color: #30271f; }

        .error {
            background: #ffeded;
            color: #9b1c1c;
            padding: 12px;
            border-radius: 6px;
        }

        .empty {
            background: #fff4dc;
            padding: 12px;
            border-radius: 6px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="topbar">
        <h1>Add a Product</h1>
        <a class="link" href="products.php">View Inventory</a>
    </div>

    <div class="panel">
        <?php if ($error): ?>
            <p class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <?php if (!$categories): ?>
            <p class="empty">
                No categories were found. Add at least one category
                to the categories table before adding products.
            </p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label for="product_name">Product Name *</label>
            <input
                id="product_name"
                name="product_name"
                type="text"
                maxlength="200"
                required
                value="<?= htmlspecialchars($_POST['product_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >

            <label for="category_id">Category</label>
            <select id="category_id" name="category_id">
                <option value="">Select a category (optional)</option>
                <?php foreach ($categories as $category): ?>
                    <option
                        value="<?= (int)$category['category_id'] ?>"
                        <?= (string)($_POST['category_id'] ?? '') ===
                            (string)$category['category_id'] ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($category['category_name'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="description">Description</label>
            <textarea
                id="description"
                name="description"
            ><?= htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>

            <label for="price">Price ($) *</label>
            <input
                id="price"
                name="price"
                type="number"
                min="0"
                step="0.01"
                required
                value="<?= htmlspecialchars($_POST['price'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >

            <label for="quantity">Inventory Quantity *</label>
            <input
                id="quantity"
                name="quantity"
                type="number"
                min="0"
                step="1"
                required
                value="<?= htmlspecialchars($_POST['quantity'] ?? '0', ENT_QUOTES, 'UTF-8') ?>"
            >

            <label for="image">Product Image</label>
            <input
                id="image"
                name="image"
                type="file"
                accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif"
            >
            <span class="hint">Optional. JPG, PNG, WEBP, or GIF; maximum 5 MB.</span>

            <button type="submit" <?= !$categories ? 'disabled' : '' ?>>
                Save Product
            </button>
        </form>
    </div>
</div>
</body>
</html>
