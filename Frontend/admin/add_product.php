<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Only administrators can add products.
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'admin'
) {
    http_response_code(403);
    exit('Access denied. Please log in with an administrator account.');
}

$error = '';

// Preserve form values after validation errors.
$name = '';
$categoryName = '';
$description = '';
$priceInput = '';
$quantityInput = '0';

// Process form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['product_name'] ?? '');
    $categoryName = trim($_POST['category_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priceInput = trim($_POST['price'] ?? '');
    $quantityInput = trim($_POST['quantity'] ?? '');

    // Validate product name.
    if ($name === '') {
        $error = 'Please enter a product name.';
    } elseif (mb_strlen($name) > 200) {
        $error = 'Product name cannot exceed 200 characters.';
    }

    // Validate price.
    if (!$error) {
        if (
            $priceInput === '' ||
            !is_numeric($priceInput) ||
            !is_finite((float)$priceInput) ||
            (float)$priceInput < 0 ||
            (float)$priceInput > 99999999.99
        ) {
            $error = 'Please enter a valid price.';
        }
    }

    // Validate quantity.
    if (!$error) {
        if (
            $quantityInput === '' ||
            filter_var($quantityInput, FILTER_VALIDATE_INT) === false ||
            (int)$quantityInput < 0
        ) {
            $error = 'Please enter a valid, non-negative quantity.';
        }
    }

    // Validate category.
    if (!$error && mb_strlen($categoryName) > 100) {
        $error = 'Category name cannot exceed 100 characters.';
    }

    // Resolve or create category.
    $categoryId = null;

    if (!$error && $categoryName !== '') {
        try {
            $stmt = $pdo->prepare(
                "SELECT category_id
                 FROM categories
                 WHERE LOWER(category_name) = LOWER(?)
                 LIMIT 1"
            );

            $stmt->execute([$categoryName]);
            $existingCategory = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingCategory) {
                $categoryId = (int)$existingCategory['category_id'];
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO categories
                     (category_name, description)
                     VALUES (?, ?)"
                );

                $stmt->execute([$categoryName, null]);
                $categoryId = (int)$pdo->lastInsertId();
            }

        } catch (PDOException $e) {

            // Check whether the category already exists.
            try {
                $stmt = $pdo->prepare(
                    "SELECT category_id
                     FROM categories
                     WHERE LOWER(category_name) = LOWER(?)
                     LIMIT 1"
                );

                $stmt->execute([$categoryName]);
                $existingCategory = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existingCategory) {
                    $categoryId = (int)$existingCategory['category_id'];
                } else {
                    $error = 'Could not find or create this category.';
                }

            } catch (PDOException $lookupError) {
                $error = 'Could not find or create this category.';
            }
        }
    }

    // Handle optional image upload.
    $imagePath = null;
    $uploadedImageFullPath = null;

    if (
        !$error &&
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $error = 'The image upload failed. Please try again.';

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

                if (
                    !is_dir($imageDirectory) &&
                    !mkdir($imageDirectory, 0755, true)
                ) {
                    $error = 'Could not create the image directory.';

                } elseif (!is_writable($imageDirectory)) {
                    $error = 'The image directory is not writable.';

                } else {

                    $filename = bin2hex(random_bytes(16))
                        . '.' . $allowedTypes[$mime];

                    $fullPath = $imageDirectory . $filename;

                    if (move_uploaded_file($tmp, $fullPath)) {
                        $imagePath = 'assets/images/' . $filename;
                        $uploadedImageFullPath = $fullPath;
                    } else {
                        $error = 'Could not save the uploaded image.';
                    }
                }
            }
        }
    }

    // Insert product into database.
    if (!$error) {

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO products
                (
                    category_id,
                    product_name,
                    description,
                    price,
                    quantity,
                    image,
                    is_active
                )
                VALUES
                (
                    :category_id,
                    :product_name,
                    :description,
                    :price,
                    :quantity,
                    :image,
                    1
                )"
            );

            $stmt->execute([
                ':category_id' => $categoryId,
                ':product_name' => $name,
                ':description' => $description,
                ':price' => number_format(
                    (float)$priceInput,
                    2,
                    '.',
                    ''
                ),
                ':quantity' => (int)$quantityInput,
                ':image' => $imagePath
            ]);

            header('Location: products.php?added=1');
            exit;

        } catch (PDOException $e) {

            // Remove uploaded image if database insertion fails.
            if (
                $uploadedImageFullPath !== null &&
                is_file($uploadedImageFullPath)
            ) {
                unlink($uploadedImageFullPath);
            }

            // Log technical error for development.
            error_log('Add product error: ' . $e->getMessage());

            $error = 'Could not add the product. Please check your database configuration and try again.';
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
    <link rel="stylesheet" href="admin.css">
    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --cream: #f7f3eb;
            --white: #ffffff;
            --brown: #382719;
            --brown-light: #60442e;
            --gold: #b89558;
            --gold-light: #e9dcc3;
            --text: #332a22;
            --muted: #81776b;
            --border: #e5ddd0;
            --error-bg: #fff0ed;
            --error-text: #a33b2d;
        }

        body {
            min-height: 100vh;
            margin: 0;
            padding: 30px 16px 50px;
            background: var(--cream);
            color: var(--text);
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 15px;
            line-height: 1.6;
        }

        .container {
            width: min(850px, 100%);
            margin: 0 auto;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 26px;
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
            font-size: clamp(27px, 4vw, 36px);
            font-weight: 600;
        }

        .subtitle {
            margin: 7px 0 0;
            color: var(--muted);
        }

        .panel {
            padding: clamp(22px, 4vw, 38px);
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 12px 35px rgba(56, 39, 25, 0.06);
        }

        .panel-heading {
            margin-bottom: 24px;
            padding-bottom: 17px;
            border-bottom: 1px solid var(--border);
        }

        .panel-heading h2 {
            margin: 0;
            color: var(--brown);
            font-family: Georgia, "Times New Roman", serif;
            font-size: 23px;
            font-weight: 600;
        }

        .panel-heading p {
            margin: 5px 0 0;
            color: var(--muted);
            font-size: 13px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0 22px;
        }

        .field {
            min-width: 0;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin: 18px 0 7px;
            color: var(--brown);
            font-size: 14px;
            font-weight: 650;
        }

        input,
        textarea {
            display: block;
            width: 100%;
            min-height: 47px;
            padding: 12px 13px;
            border: 1px solid #ddd4c7;
            border-radius: 8px;
            background: #fffefa;
            color: var(--text);
            font: inherit;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        input:focus,
        textarea:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(184, 149, 88, 0.15);
        }

        input[type="file"] {
            padding: 10px;
            border-style: dashed;
            background: #faf7f1;
            cursor: pointer;
        }

        input[type="file"]::file-selector-button {
            margin-right: 12px;
            padding: 8px 12px;
            background: var(--gold-light);
            border: 0;
            border-radius: 5px;
            color: var(--brown);
            font-weight: 600;
            cursor: pointer;
        }

        .hint {
            display: block;
            margin-top: 7px;
            color: var(--muted);
            font-size: 12px;
        }

        .error {
            margin-bottom: 20px;
            padding: 13px 16px;
            background: var(--error-bg);
            border-left: 4px solid var(--error-text);
            border-radius: 6px;
            color: var(--error-text);
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
            padding-top: 22px;
            border-top: 1px solid var(--border);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 11px 19px;
            border: 1px solid var(--border);
            border-radius: 7px;
            font: inherit;
            font-size: 14px;
            font-weight: 650;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s,
                        transform 0.2s;
        }

        .btn-primary {
            background: var(--brown);
            border-color: var(--brown);
            color: white;
        }

        .btn-primary:hover {
            background: var(--brown-light);
            border-color: var(--gold);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: white;
            color: var(--brown);
        }

        .btn-secondary:hover {
            background: #fcf8f0;
            border-color: var(--gold);
        }

        @media (max-width: 600px) {
            body {
                padding: 20px 12px 35px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .full-width {
                grid-column: auto;
            }

            .topbar .btn,
            .actions .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <header class="topbar">

        <div>
            <div class="eyebrow">AFGHAN ANTIQUE STORE</div>
            <h1>Add a Product</h1>
            <p class="subtitle">
                Add a new item to your antique collection.
            </p>
        </div>

        <a href="products.php" class="btn btn-secondary">
            View Inventory
        </a>

    </header>

    <main class="panel">

        <div class="panel-heading">
            <h2>Product Information</h2>
            <p>Enter the details below to add a product to your inventory.</p>
        </div>

        <?php if ($error): ?>
            <div class="error" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <div class="form-grid">

                <div class="field full-width">
                    <label for="product_name">Product Name *</label>

                    <input
                        id="product_name"
                        name="product_name"
                        type="text"
                        maxlength="200"
                        placeholder="e.g., Afghan Antique Mantel Clock"
                        required
                        value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="field full-width">
                    <label for="category_name">Category</label>

                    <input
                        id="category_name"
                        name="category_name"
                        type="text"
                        maxlength="100"
                        placeholder="e.g., Clocks, Furniture, Afghan Carpets"
                        value="<?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') ?>"
                    >

                    <span class="hint">
                        Enter an existing category or type a new category name.
                        New categories are created automatically.
                    </span>
                </div>

                <div class="field full-width">
                    <label for="description">Product Description</label>

                    <textarea
                        id="description"
                        name="description"
                        placeholder="Describe the product, its style, materials, and history..."
                    ><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="field">
                    <label for="price">Price ($) *</label>

                    <input
                        id="price"
                        name="price"
                        type="number"
                        min="0"
                        max="99999999.99"
                        step="0.01"
                        placeholder="0.00"
                        required
                        value="<?= htmlspecialchars($priceInput, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="field">
                    <label for="quantity">Inventory Quantity *</label>

                    <input
                        id="quantity"
                        name="quantity"
                        type="number"
                        min="0"
                        step="1"
                        required
                        value="<?= htmlspecialchars($quantityInput, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="field full-width">
                    <label for="image">Product Image</label>

                    <input
                        id="image"
                        name="image"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif"
                    >

                    <span class="hint">
                        Optional. JPG, PNG, WEBP, or GIF. Maximum file size: 5 MB.
                    </span>
                </div>

            </div>

            <div class="actions">

                <button type="submit" class="btn btn-primary">
                    Save Product
                </button>

                <a href="products.php" class="btn btn-secondary">
                    Cancel
                </a>

            </div>

        </form>

    </main>

</div>

</body>
</html>
