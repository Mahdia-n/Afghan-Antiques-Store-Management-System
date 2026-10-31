<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!-- Navigation Authentication Links -->

<?php if (isset($_SESSION['user_id'])): ?>

    <?php if (
        isset($_SESSION['user_role']) &&
        $_SESSION['user_role'] === 'admin'
    ): ?>

        <a href="admin/dashboard.php" class="nav-link">
            Admin Dashboard
        </a>

    <?php endif; ?>

    <a href="logout.php" class="nav-link">
        Sign Out
    </a>

<?php else: ?>

    <a href="login.php" class="nav-link">
        Sign In
    </a>

    <a href="register.php" class="nav-link">
        Register
    </a>

<?php endif; ?>