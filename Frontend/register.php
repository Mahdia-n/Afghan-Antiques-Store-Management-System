<?php
session_start();

$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? 'index.php';

$allowedRedirects = ['index.php', 'checkout.php'];

if (!in_array($redirect, $allowedRedirects, true)) {
    $redirect = 'index.php';
}

require_once __DIR__ . '/config/database.php';

$errors = [];
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

    // Validate CSRF token.
    if (
        !is_string($csrfToken) ||
        !hash_equals($_SESSION['csrf_token'], $csrfToken)
    ) {
        $errors[] = 'Your session expired. Please refresh the page and try again.';
    }

    // Validate name.
    if ($name === '') {
        $errors[] = 'Please enter your full name.';
    } elseif (strlen($name) > 150) {
        $errors[] = 'Your name must not exceed 150 characters.';
    }

    // Validate email.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (strlen($email) > 255) {
        $errors[] = 'Your email address is too long.';
    }

    // Validate password.
    if (strlen($password) < 8) {
        $errors[] = 'Your password must be at least 8 characters.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Your passwords do not match.';
    }

    // Create the customer account.
    if (empty($errors)) {
        try {
            $check = $pdo->prepare(
                'SELECT user_id FROM users WHERE email = ? LIMIT 1'
            );
            $check->execute([$email]);

            if ($check->fetch()) {
                $errors[] = 'This email address is already registered. Please log in.';
            } else {
                $passwordHash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $insert = $pdo->prepare(
                    'INSERT INTO users
                        (full_name, email, password_hash, role)
                     VALUES (?, ?, ?, ?)'
                );

                $insert->execute([
                    $name,
                    $email,
                    $passwordHash,
                    'customer'
                ]);

                // Refresh the CSRF token.
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                // Continue to login while preserving the destination.
                header(
                    'Location: login.php?redirect=' . urlencode($redirect)
                );
                exit;
            }
        } catch (PDOException $e) {
            error_log('Registration error: ' . $e->getMessage());

            if ($e->getCode() === '23000') {
                $errors[] = 'This email address may already be registered. Please log in.';
            } else {
                $errors[] = 'We could not create your account right now. Please try again later.';
            }
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
                name="redirect"
                value="<?= htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8') ?>"
            >

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
                maxlength="150"
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
            <a href="login.php?redirect=<?= urlencode($redirect) ?>">Log in</a>
        </p>
    </section>
</main>

</body>
</html>