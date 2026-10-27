<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
?>

<?php
session_start();

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($name === '') {
        $errors[] = 'Please enter your full name.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Your password must be at least 8 characters.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Your passwords do not match.';
    }

    // Temporary frontend demo only: no account is saved.
    if (!$errors) {
        $errors[] = 'The registration form is working, but account creation will be enabled after database configuration.';
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

        <?php if ($errors): ?>
            <div class="auth-message" role="alert">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="register.php" class="auth-form">
            <label for="name">Full name</label>
            <input
                type="text"
                id="name"
                name="name"
                value="<?= htmlspecialchars($name) ?>"
                autocomplete="name"
                placeholder="Enter your full name"
                required
            >

            <label for="email">Email address</label>
            <input
                type="email"
                id="email"
                name="email"
                value="<?= htmlspecialchars($email) ?>"
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
