<nav class="navbar">
    <a href="index.php" class="logo">Antique Store</a>

    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="products.php">Products</a>

        <?php if (isset($_SESSION['customer_id'])): ?>
            <span class="welcome">
                Welcome!
            </span>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="signup-btn">Sign Up</a>
        <?php endif; ?>

        <a href="cart.php">Cart</a>
    </div>
</nav>