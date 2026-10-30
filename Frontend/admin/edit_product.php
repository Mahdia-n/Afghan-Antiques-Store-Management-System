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

// CSRF protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function h($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit('Invalid product ID.');
}

$error = '';
$success = '';

try {
    $categoryStmt = $pdo->query(
        "SELECT category_id, category_name
         FROM categories
         ORDER BY category_name"
    );
    $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare(
        "SELECT product_id, category_id, product_name, description,
                price, quantity, image, is_active
         FROM products
         WHERE product_id = ?"
    );
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        http_response_code(404);
        exit('Product not found.');
    }
} catch (PDOException $e) {
    http_response_code(500);
    exit('Unable to load product information.');
}

// Default form values
$name = $product['product_name'];
$description = $product['description'] ?? '';
$price = $product['price'];
$quantity = $product['quantity'];
$categoryId = $product['category_id'];
$isActive = (int)$product['is_active'];
$imagePath = $product['image'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Your session expired or the form was invalid. Please refresh and try again.';
    }

    $name = trim($_POST['product_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priceInput = trim($_POST['price'] ?? '');
    $quantityInput = trim($_POST['quantity'] ?? '');
    $categoryInput = $_POST['category_id'] ?? '';
    $statusInput = $_POST['is_active'] ?? '';

    if (!$error && $name === '') {
        $error = 'Product name is required.';
    } elseif (
        !$error &&
        (strlen($name) > 200)
    ) {
        $error = 'Product name cannot exceed 200 characters.';
    } elseif (
        !$error &&
        ($priceInput === '' ||
         !is_numeric($priceInput) ||
         !is_finite((float)$priceInput) ||
         (float)$priceInput < 0 ||
         (float)$priceInput > 99999999.99)
    ) {
        $error = 'Enter a valid price.';
    } elseif (
        !$error &&
        ($quantityInput === '' ||
         filter_var($quantityInput, FILTER_VALIDATE_INT) === false ||
         (int)$quantityInput < 0)
    ) {
        $error = 'Enter a valid, non-negative quantity.';
    }

    // Validate category
    $newCategoryId = null;

    if (!$error && $categoryInput !== '') {
        $newCategoryId = filter_var(
            $categoryInput,
            FILTER_VALIDATE_INT
        );

        if ($newCategoryId === false) {
            $error = 'Please select a valid category.';
        } else {
            $check = $pdo->prepare(
                "SELECT COUNT(*) FROM categories WHERE category_id = ?"
            );
            $check->execute([$newCategoryId]);

            if (!$check->fetchColumn()) {
                $error = 'The selected category does not exist.';
            }
        }
    }

    // Validate product status
    if (!$error && !in_array($statusInput, ['0', '1'], true)) {
        $error = 'Please select a valid product status.';
    }

    $newImagePath = $imagePath;
    $uploadedNewImage = false;

    // Upload a new image only when a file was selected.
    if (
        !$error &&
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Image upload failed.';
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

                    if (move_uploaded_file(
                        $tmp,
                        $imageDirectory . $filename
                    )) {
                        $newImagePath = 'assets/images/' . $filename;
                        $uploadedNewImage = true;
                    } else {
                        $error = 'Could not save the uploaded image.';
                    }
                }
            }
        }
    }

    // Update the database
    if (!$error) {
        try {
            $update = $pdo->prepare(
                "UPDATE products
                 SET category_id = :category_id,
                     product_name = :product_name,
                     description = :description,
                     price = :price,
                     quantity = :quantity,
                     image = :image,
                     is_active = :is_active
                 WHERE product_id = :product_id"
            );

            $update->execute([
                ':category_id' => $newCategoryId,
                ':product_name' => $name,
                ':description' => $description,
                ':price' => number_format((float)$priceInput, 2, '.', ''),
                ':quantity' => (int)$quantityInput,
                ':image' => $newImagePath,
                ':is_active' => (int)$statusInput,
                ':product_id' => $id
            ]);

            // Keep the old image file; deleting files can break
            // other pages if an image path is reused.
            header('Location: products.php?updated=1');
            exit;
        } catch (PDOException $e) {
            if ($uploadedNewImage) {
                $savedImage = __DIR__ . '/../' . $newImagePath;
                if (is_file($savedImage)) {
                    unlink($savedImage);
                }
            }

            $error = 'Could not update the product. Please try again.';
        }
    }

    // Preserve the submitted values if validation fails.
    $categoryId = $categoryInput === '' ? null : $categoryInput;

    if (in_array($statusInput, ['0', '1'], true)) {
        $isActive = (int)$statusInput;
    }

    if ($uploadedNewImage && $error) {
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
    <title>Edit Product | Afghan Antique Store>

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
        }

        body {
            margin: 0;
            padding: 30px 16px 50px;
            background: var(--cream);
            color: var(--text);
            font-family: "Segoe UI", Arial, sans-serif;
            line-height: 1.6;
        }

        .container {
            max-width: 900px;
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

            .header .btn {
                width: 100%;
            }

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

        <a href="products.php" class="btn">← Back to Inventory</a>
    </header>

    <main class="panel">
        <h2 class="panel-title">
            Product #<?= (int)$product['product_id'] ?>
        </h2>

        <?php if ($error): ?>
            <div class="error">
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

                <div class="field">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id">
                        <option value="">Uncategorized</option>
                        <?php foreach ($categories as $category): ?>
                            <option
                                value="<?= (int)$category['category_id'] ?>"
                                <?= (string)$categoryId ===
                                    (string)$category['category_id']
                                    ? 'selected' : '' ?>
                            >
                                <?= h($category['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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
                    <label for="description">Description</label>
                    <textarea
                        id="description"
                        name="description"
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
                        JPG, PNG, WEBP, or GIF; maximum 5 MB.
                    </span>

                    <div class="image-preview">
                        <img
                            id="imagePreview"
                            src="<?= !empty($imagePath)
                                ? '../' . h($imagePath)
                                : '' ?>"
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

        if (!allowedTypes.includes(file.type) ||
            file.size > 5 * 1024 * 1024) {
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
