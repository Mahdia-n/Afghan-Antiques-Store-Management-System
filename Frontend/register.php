<?php
session_start();
require_once __DIR__ . '/db.php';

$errors = [];
$success = '';
$name = '';
$email = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!is_string($csrfToken) ||
        !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    }

    if ($name === '') {
        $errors[] = 'Please enter your full name.';
    } elseif (strlen($name) > 100) {
        $errors[] = 'Your name must not exceed 100 characters.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (strlen($email) > 255) {
        $errors[] = 'Your email address is too long.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Your password must be at least 8 characters.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Your passwords do not match.';
    }

    if (empty($errors)) {
        try {
            $check = $pdo->prepare(
                'SELECT id FROM Users WHERE email = ? LIMIT 1'
            );
            $check->execute([$email]);

            if ($check->fetch()) {
                $errors[] = 'This email address is already registered. Please log in.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $insert = $pdo->prepare(
                    'INSERT INTO Users (full_name, email, password, role)
                     VALUES (?, ?, ?, ?)'
                );

                $insert->execute([
                    $name,
                    $email,
                    $passwordHash,
                    'Customer'
                ]);

                $success = 'Your account has been created successfully! You can now log in.';
                $name = '';
                $email = '';

                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
        } catch (PDOException $e) {
            error_log('Registration error: ' . $e->getMessage());
            $errors[] = 'We could not create your account right now. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Afghan Antiques</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">

<header class="auth-header">
    <a href="index.php" class="auth-brand">Afghan Antiques</a>
    <a href="products.php" class="auth-back">Browse Collection</a>
</header>

<main class="auth-container">
    <section class="auth-card">
        <p class="auth-eyebrow">JOIN OUR COMMUNITY</p>
        <h1>Create your account</h1>
        <p class="auth-subtitle">
            Create an account to enjoy a more personal shopping experience.
        </p>

        <?php if ($success !== ''): ?>
            <div class="auth-message" role="status">
                <p><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
                <p><a href="login.php">Log in to your account</a></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="auth-message" role="alert">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="register.php" class="auth-form">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"
            >

            <label for="name">Full name</label>
            <input
                type="text"
                id="name"
                name="name"
                value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                maxlength="100"
                autocomplete="name"
                placeholder="Enter your full name"
                required
            >

            <label for="email">Email address</label>
            <input
                type="email"
                id="email"
                name="email"
                value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                maxlength="255"
                autocomplete="email"
                placeholder="you@example.com"
                required
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                minlength="8"
                autocomplete="new-password"
                placeholder="At least 8 characters"
                required
            >

            <label for="confirm_password">Confirm password</label>
            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                minlength="8"
                autocomplete="new-password"
                placeholder="Enter your password again"
                required
            >

            <button type="submit" class="auth-submit">
                Create Account
            </button>
        </form>

        <p class="auth-switch">
            Already have an account?
            <a href="login.php">Log in</a>
        </p>
    </section>
</main>

</body>
</html>