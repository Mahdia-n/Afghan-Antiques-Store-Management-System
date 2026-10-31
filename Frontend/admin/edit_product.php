<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Only administrators can edit products.
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'admin'
) {
    http_response_code(403);
    exit('Access denied. Please log in as an administrator.');
}

// Generate CSRF token.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Escape output.
function h($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// Validate product ID.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit('Invalid product ID.');
}

$error = '';

// Load product.
try {
    $stmt = $pdo->prepare(
        "SELECT *
         FROM products
         WHERE product_id = ?
         LIMIT 1"
    );

    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        http_response_code(404);
        exit('Product not found.');
    }

} catch (PDOException $e) {
    error_log('Edit product loading error: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to load product information.');
}

// Default form values.
$name = $product['product_name'];
$categoryName = '';
$description = $product['description'] ?? '';
$price = $product['price'];
$quantity = $product['quantity'];
$isActive = (int)$product['is_active'];
$imagePath = $product['image'] ?? '';

// Load existing category name.
if (!empty($product['category_id'])) {
    try {
        $stmt = $pdo->prepare(
            "SELECT category_name
             FROM categories
             WHERE category_id = ?"
        );

        $stmt->execute([$product['category_id']]);
        $categoryName = $stmt->fetchColumn() ?: '';

    } catch (PDOException $e) {
        error_log('Category loading error: ' . $e->getMessage());
    }
}

// Process form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verify CSRF token.
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        $error = 'Invalid security token. Please refresh and try again.';
    }

    // Get submitted values.
    $name = trim($_POST['product_name'] ?? '');
    $categoryName = trim($_POST['category_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priceInput = trim($_POST['price'] ?? '');
    $quantityInput = trim($_POST['quantity'] ?? '');
    $statusInput = $_POST['is_active'] ?? '';

    // Validate product name.
    if (!$error && $name === '') {
        $error = 'Product name is required.';
    } elseif (!$error && mb_strlen($name) > 200) {
        $error = 'Product name cannot exceed 200 characters.';
    }

    // Validate category.
    if (!$error && mb_strlen($categoryName) > 100) {
        $error = 'Category name cannot exceed 100 characters.';
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
            $error = 'Please enter a valid quantity.';
        }
    }

    // Validate product status.
    if (
        !$error &&
        !in_array($statusInput, ['0', '1'], true)
    ) {
        $error = 'Please select a valid product status.';
    }

    // Resolve or create category.
    $newCategoryId = null;

    if (!$error && $categoryName !== '') {

        try {
            // Look for an existing category.
            $stmt = $pdo->prepare(
                "SELECT category_id
                 FROM categories
                 WHERE LOWER(category_name) = LOWER(?)
                 LIMIT 1"
            );

            $stmt->execute([$categoryName]);

            $existingCategory = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingCategory) {

                $newCategoryId = (int)$existingCategory['category_id'];

            } else {

                // Create a new category.
                $stmt = $pdo->prepare(
                    "INSERT INTO categories
                     (category_name, description)
                     VALUES (?, ?)"
                );

                $stmt->execute([$categoryName, null]);

                $newCategoryId = (int)$pdo->lastInsertId();
            }

        } catch (PDOException $e) {

            // Check if another request created the category.
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
                    $newCategoryId = (int)$existingCategory['category_id'];
                } else {
                    $error = 'Could not find or create the category.';
                }

            } catch (PDOException $lookupError) {
                $error = 'Could not find or create the category.';
            }
        }
    }

    // Keep current image unless a new image is uploaded.
    $newImagePath = $imagePath;
    $uploadedNewImage = false;
    $uploadedImageFullPath = null;

    // Handle image upload.
    if (
        !$error &&
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

            $error = 'Image upload failed. Please try again.';

        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {

            $error = 'Image must be smaller than 5 MB.';

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

                        $newImagePath = 'assets/images/' . $filename;
                        $uploadedImageFullPath = $fullPath;
                        $uploadedNewImage = true;

                    } else {

                        $error = 'Could not save the uploaded image.';
                    }
                }
            }
        }
    }

    // Update product in database.
    if (!$error) {

        try {
            $stmt = $pdo->prepare(
                "UPDATE products
                 SET
                    category_id = :category_id,
                    product_name = :product_name,
                    description = :description,
                    price = :price,
                    quantity = :quantity,
                    image = :image,
                    is_active = :is_active
                 WHERE product_id = :product_id"
            );

            $stmt->execute([
                ':category_id' => $newCategoryId,
                ':product_name' => $name,
                ':description' => $description,
                ':price' => number_format(
                    (float)$priceInput,
                    2,
                    '.',
                    ''
                ),
                ':quantity' => (int)$quantityInput,
                ':image' => $newImagePath,
                ':is_active' => (int)$statusInput,
                ':product_id' => $id
            ]);

            // Update successful.
            header('Location: products.php?updated=1');
            exit;

        } catch (PDOException $e) {

            // Remove newly uploaded image if update fails.
            if (
                $uploadedImageFullPath !== null &&
                is_file($uploadedImageFullPath)
            ) {
                unlink($uploadedImageFullPath);
            }

            error_log('Edit product update error: ' . $e->getMessage());

            $error = 'Could not update the product. Please try again.';
        }
    }

    // Preserve submitted status after an error.
    if (in_array($statusInput, ['0', '1'], true)) {
        $isActive = (int)$statusInput;
    }

    // Keep old image if submission fails.
    if ($error && $uploadedNewImage) {
        $newImagePath = $imagePath;
    }

    $imagePath = $newImagePath;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Product | Afghan Antique Store</title>

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
        }

        body {
            min-height: 100vh;
            margin: 0;
            padding: 30px 16px 50px;
            background: var(--cream);
            color: var(--text);
            font-family: "Segoe UI", Arial, sans-serif;
            line-height: 1.6;
        }

        .container {
            width: min(900px, 100%);
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 25px;
        }

        .eyebrow {
            color: var(--gold);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 2px;
        }

        h1 {
            margin: 5px 0;
            color: var(--brown);
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(27px, 4vw, 35px);
            font-weight: 600;
        }

        .subtitle {
            margin: 5px 0 0;
            color: var(--muted);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 45px;
            padding: 11px 17px;
            border: 1px solid var(--border);
            border-radius: 7px;
            background: white;
            color: var(--brown);
            font: inherit;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .btn:hover {
            border-color: var(--gold);
            background: #fcf8f0;
        }

        .btn-primary {
            background: var(--brown);
            border-color: var(--brown);
            color: white;
        }

        .btn-primary:hover {
            background: var(--brown-light);
            color: white;
        }

        .panel {
            padding: clamp(22px, 4vw, 38px);
            background: white;
            border: 1px solid var(--border);
            border-radius: 13px;
            box-shadow: 0 10px 30px rgba(56, 39, 25, 0.05);
        }

        .panel-title {
            margin: 0 0 22px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
            color: var(--brown);
            font-family: Georgia, "Times New Roman", serif;
            font-size: 23px;
            font-weight: 600;
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
            margin: 17px 0 7px;
            color: var(--brown);
            font-size: 14px;
            font-weight: 650;
        }

        input,
        select,
        textarea {
            width: 100%;
            min-height: 46px;
            padding: 12px 13px;
            border: 1px solid #ddd4c7;
            border-radius: 7px;
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
        select:focus,
        textarea:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(184, 149, 88, 0.15);
        }

        input[type="file"] {
            padding: 10px;
            border-style: dashed;
            background: #faf7f1;
        }

        .hint {
            display: block;
            margin-top: 7px;
            color: var(--muted);
            font-size: 12px;
        }

        .image-preview {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-top: 12px;
            padding: 13px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: #faf7f1;
        }

        .image-preview img {
            width: 95px;
            height: 95px;
            border-radius: 7px;
            border: 1px solid var(--border);
            background: white;
            object-fit: cover;
        }

        .image-description {
            color: var(--muted);
            font-size: 13px;
        }

        .error {
            margin-bottom: 20px;
            padding: 13px 16px;
            border-left: 4px solid #a33b2d;
            border-radius: 6px;
            background: #fff0ed;
            color: #a33b2d;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 27px;
            padding-top: 22px;
            border-top: 1px solid var(--border);
        }

        @media (max-width: 620px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .full-width {
                grid-column: auto;
            }

            .header .btn,
            .actions .btn {
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
            <h1>Edit Product</h1>
            <p class="subtitle">
                Update the details and stock of your antique item.
            </p>
        </div>

        <a href="products.php" class="btn">
            Back to Inventory
        </a>

    </header>

    <main class="panel">

        <h2 class="panel-title">
            Product #<?= (int)$product['product_id'] ?>
        </h2>

        <?php if ($error): ?>
            <div class="error" role="alert">
                <?= h($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= h($_SESSION['csrf_token']) ?>"
            >

            <div class="form-grid">

                <div class="field full-width">
                    <label for="product_name">Product Name *</label>

                    <input
                        type="text"
                        id="product_name"
                        name="product_name"
                        maxlength="200"
                        required
                        value="<?= h($name) ?>"
                    >
                </div>

                <div class="field full-width">
                    <label for="category_name">Category</label>

                    <input
                        type="text"
                        id="category_name"
                        name="category_name"
                        maxlength="100"
                        placeholder="e.g., Clocks, Furniture, Afghan Carpets"
                        value="<?= h($categoryName) ?>"
                    >

                    <span class="hint">
                        Enter an existing category or type a new category.
                        New categories are created automatically.
                        Leave blank for Uncategorized.
                    </span>
                </div>

                <div class="field">
                    <label for="is_active">Product Status *</label>

                    <select id="is_active" name="is_active" required>
                        <option value="1" <?= $isActive === 1 ? 'selected' : '' ?>>
                            Active
                        </option>

                        <option value="0" <?= $isActive === 0 ? 'selected' : '' ?>>
                            Inactive
                        </option>
                    </select>
                </div>

                <div class="field">
                    <label for="price">Price ($) *</label>

                    <input
                        type="number"
                        id="price"
                        name="price"
                        min="0"
                        max="99999999.99"
                        step="0.01"
                        required
                        value="<?= h($price) ?>"
                    >
                </div>

                <div class="field">
                    <label for="quantity">Stock Quantity *</label>

                    <input
                        type="number"
                        id="quantity"
                        name="quantity"
                        min="0"
                        step="1"
                        required
                        value="<?= h($quantity) ?>"
                    >
                </div>

                <div class="field full-width">
                    <label for="description">Product Description</label>

                    <textarea
                        id="description"
                        name="description"
                        placeholder="Describe the product, its style, materials, and history..."
                    ><?= h($description) ?></textarea>
                </div>

                <div class="field full-width">
                    <label for="image">Replace Product Image</label>

                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif"
                    >

                    <span class="hint">
                        Optional. Leave empty to keep the current image.
                        JPG, PNG, WEBP, or GIF. Maximum 5 MB.
                    </span>

                    <div class="image-preview">

                        <img
                            id="imagePreview"
                            src="<?= !empty($imagePath) ? '../' . h($imagePath) : '' ?>"
                            alt="Current product image"
                            <?= empty($imagePath) ? 'hidden' : '' ?>
                        >

                        <div class="image-description" id="imageDescription">
                            <?= !empty($imagePath)
                                ? 'Current product image'
                                : 'No product image uploaded yet.' ?>
                        </div>

                    </div>
                </div>

            </div>

            <div class="actions">

                <button type="submit" class="btn btn-primary">
                    Save Changes
                </button>

                <a href="products.php" class="btn">
                    Cancel
                </a>

            </div>

        </form>

    </main>

</div>

<script>
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');
    const imageDescription = document.getElementById('imageDescription');

    imageInput.addEventListener('change', function () {

        const file = this.files[0];

        if (!file) return;

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif'
        ];

        if (
            !allowedTypes.includes(file.type) ||
            file.size > 5 * 1024 * 1024
        ) {
            alert('Choose a JPG, PNG, WEBP, or GIF image smaller than 5 MB.');
            this.value = '';
            return;
        }

        imagePreview.src = URL.createObjectURL(file);
        imagePreview.hidden = false;
        imageDescription.textContent = 'New image preview';
    });
</script>

</body>
</html>
