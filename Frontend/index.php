<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Antique Store | Timeless Treasures';
$cartCount = (int) ($_SESSION['cart_count'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Discover unique antiques, vintage furniture, collectible clocks, and timeless decorative treasures.">
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
  
</head>

<body>

<header class="site-header">
    <a href="index.php" class="brand">
        <span class="brand-mark">A</span>
        <span>
            <span class="brand-name">Antique Store</span>
            <span class="brand-tagline">TIMELESS TREASURES</span>
        </span>
    </a>

    <button class="menu-toggle" id="menuToggle"
            type="button" aria-label="Toggle navigation"
            aria-expanded="false">☰</button>

    <nav class="nav-links" id="navLinks">
        <a href="index.php">Home</a>
        <a href="products.php">Products</a>

        <?php if (isset($_SESSION['customer_id'])): ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="signup-button">Sign Up</a>
        <?php endif; ?>

        <a href="cart.php" class="cart-link">
            Cart <span class="cart-count"><?= $cartCount ?></span>
        </a>
    </nav>
</header>

<main>
    <section class="hero">
        <div class="hero-content">
            <p class="eyebrow">DISCOVER AFGHAN HERITAGE</p>

            <h1>
                Afghan Antiques<br>
                <span>Preserving History & Culture</span>
            </h1>

            <p class="hero-description">
                Explore a collection inspired by Afghanistan's rich cultural
                heritage, featuring traditional decorative arts, handcrafted
                objects, vintage furniture, and timeless collectibles.
                Discover the stories and craftsmanship behind every piece.
            </p>

            <a href="products.php" class="primary-button">
                Explore Collection <span aria-hidden="true">→</span>
            </a>
        </div>

        <p class="hero-note">AFGHAN HERITAGE · TRADITIONAL CRAFTSMANSHIP · TIMELESS TREASURES</p>
    </section>

    <section class="featured-section">
        <div class="section-heading">
            <div class="ornament">✧ ── ❦ ── ✧</div>
            <p class="eyebrow">EXPLORE OUR COLLECTION</p>
            <h2>Featured Antiques Products</h2>
            <p>Discover decorative pieces, traditional designs, and vintage 
                asures inspired by Afghan heritage.</p>
        </div>

        <div class="product-grid">
            <article class="product-card">
                <div class="product-art clock">
                    <img src="assets/images/clock.png" alt="Antique Mantel Clock">
                </div>
                <div class="product-info">
                    <p class="product-category">TIMEPIECES</p>
                    <h3>Antique Mantel Clock</h3>
                    <p class="product-description">A timeless accent inspired by traditional craftsmanship.</p>
                    <p class="product-price">$120.00</p>
                    <a href="products.php" class="product-button">Explore Collection</a>
                </div>
            </article>

            <article class="product-card">
                <div class="product-art vase">
                    <img src="assets/images/vase.png" alt="Classic Ceramic Vase">
                </div>
                <div class="product-info">
                    <p class="product-category">CERAMICS</p>
                    <h3>Classic Ceramic Vase</h3>
                    <p class="product-description">An elegant decorative piece for a classic interior.</p>
                    <p class="product-price">$95.00</p>
                    <a href="products.php" class="product-button">Explore Collection</a>
                </div>
            </article>

            <article class="product-card">
                <div class="product-art cabinet">
                    <img src="assets/images/cabinet.png" alt="Vintage Wooden Cabinet">
                </div>
                <div class="product-info">
                    <p class="product-category">FURNITURE</p>
                    <h3>Vintage Wooden Cabinet</h3>
                    <p class="product-description">Warm wood tones and enduring vintage character.</p>
                    <p class="product-price">$450.00</p>
                    <a href="products.php" class="product-button">Explore Collection</a>
                </div>
            </article>

            <article class="product-card">
                <div class="product-art lamp">
                    <img src="assets/images/lamp.png" alt="Victorian Table Lamp">
                </div>
                <div class="product-info">
                    <p class="product-category">LIGHTING</p>
                    <h3>Victorian Table Lamp</h3>
                    <p class="product-description">Create a warm atmosphere with vintage-inspired lighting.</p>
                    <p class="product-price">$180.00</p>
                    <a href="products.php" class="product-button">Explore Collection</a>
                </div>
            </article>
        </div>

        <div class="collection-action">
            <a href="products.php" class="primary-button">
                Explore All Antiques <span aria-hidden="true">→</span>
            </a>
        </div>
    </section>

    <section class="story-section">
        <div>
            <p class="eyebrow">CELEBRATING AFGHAN HERITAGE</p>
            <h2>Every Piece Tells a Story</h2>
        </div>

        <p>
            Afghanistan has a rich tradition of craftsmanship, decorative
            arts, and cultural exchange. Our Afghan Antique Store Management
            System helps organize and present antique products, including
            their descriptions, categories, prices, and availability.
            Our goal is to make managing and discovering cultural treasures
            more organized, accessible, and convenient.
        </p>
    </section>
</main>

<footer class="site-footer">
    <div class="footer-content">
        <div>
            <a href="index.php" class="footer-brand">Antique Store</a>
            <p>Timeless treasures, carefully collected.</p>
            <p>Discover pieces that tell a story.</p>
        </div>

        <div>
            <h3>Explore</h3>
            <a href="index.php">Home</a>
            <a href="products.php">Products</a>
            <a href="register.php">Create an Account</a>
        </div>

        <div>
            <h3>Your Account</h3>
            <a href="login.php">Login</a>
            <a href="cart.php">Shopping Cart</a>
        </div>

        <div>
            <h3>Contact Us</h3>
            <p>Email: info@antiquestore.com</p>
        </div>
    </div>

    <div class="footer-bottom">
        &copy; <?= date('Y') ?> Antique Store. All rights reserved.
    </div>
</footer>

<script>
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.getElementById('navLinks');

    menuToggle.addEventListener('click', function () {
        const isOpen = navLinks.classList.toggle('open');
        menuToggle.setAttribute('aria-expanded', String(isOpen));
    });
</script>

</body>
</html>
