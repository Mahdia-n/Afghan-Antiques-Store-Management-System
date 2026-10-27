<?php
session_start();
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? 'index.php';

// Only allow internal pages as redirect destinations.
$allowedRedirects = ['index.php', 'checkout.php'];

if (!in_array($redirect, $allowedRedirects, true)) {
    $redirect = 'index.php';
}
require_once __DIR__ . '/config/database.php';

$error = '';
$email = '';

// Redirect customers who are already logged in.
if (isset($_SESSION['user_id'])) {
   header('Location: ' . $redirect);
   exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Please enter a valid email address and password.';
    } else {
        try {
            // Find the customer by email.
            $stmt = $pdo->prepare(
                'SELECT user_id, full_name, email, password_hash, role
                 FROM users
                 WHERE email = :email
                 LIMIT 1'
            );

            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            // Verify the submitted password against the stored hash.
            if ($user && password_verify($password, $user['password_hash'])) {
                // Prevent session fixation.
                session_regenerate_id(true);

                $_SESSION['user_id'] = (int) $user['user_id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                header('Location: index.php');
                exit;
            }

            // Use the same message for unknown emails and wrong passwords.
            $error = 'Invalid email address or password.';
        } catch (PDOException $e) {
            error_log('Login error: ' . $e->getMessage());
            $error = 'We could not log you in right now. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In | Afghan Antiques</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">

<header class="auth-header">
    <a href="index.php" class="auth-brand">Afghan Antiques</a>
    <a href="products.php" class="auth-back">Browse Collection</a>
</header>

<main class="auth-container">
    <section class="auth-card">
        <p class="auth-eyebrow">WELCOME BACK</p>
        <h1>Welcome back</h1>
        <p class="auth-subtitle">
            Log in to continue exploring our collection of timeless treasures.
        </p>

        <?php if ($error !== ''): ?>
            <div class="auth-message" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="post" action="login.php" class="auth-form">
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
                autocomplete="current-password"
                placeholder="Enter your password"
                required
            >

            <button type="submit" class="auth-submit">Log In</button>
        </form>

        <p class="auth-switch">
            Don't have an account?
            <a href="register.php">Sign up</a>
        </p>
    </section>
</main>

</body>
</html>
