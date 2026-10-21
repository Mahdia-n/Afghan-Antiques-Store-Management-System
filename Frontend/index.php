
<?php
$pageTitle = 'Antique Store | Timeless Treasures';
require __DIR__ . '/includes/header.php';
?>

<main>
    <section class="hero">
        <div class="hero-overlay"></div>

        <div class="hero-content">
            <p class="eyebrow">DISCOVER THE BEAUTY OF THE PAST</p>

            <h1>
                Authentic Antiques
                <br>
                <span>for a Timeless Home</span>
            </h1>

            <p class="hero-description">
                Explore our curated collection of rare and unique antiques,
                each with a story to tell.
            </p>

            <a href="products.php" class="primary-button">
                Purchase Now <span>→</span>
            </a>
        </div>
    </section>

    <section class="featured-section">
        <div class="section-heading">
            <p class="eyebrow">HANDPICKED FOR YOU</p>
            <h2>Featured Antiques</h2>
            <p>Discover timeless pieces to bring history into your home.</p>
        </div>

        <div class="product-grid">
            <article class="product-card">
                <img src="assets/images/antique-clock.jpg"
                     alt="Antique mantel clock">
                <div class="product-info">
                    <p class="product-category">TIMEPIECES</p>
                    <h3>Antique Mantel Clock</h3>
                    <p class="product-price">$120.00</p>
                    <a href="products.php" class="product-button">Explore Collection</a>
                </div>
            </article>

            <article class="product-card">
                <img src="assets/images/ceramic-vase.jpg"
                     alt="Classic ceramic vase">
                <div class="product-info">
                    <p class="product-category">CERAMICS</p>
                    <h3>Classic Ceramic Vase</h3>
                    <p class="product-price">$95.00</p>
                    <a href="products.php" class="product-button">Explore Collection</a>
                </div>
            </article>

            <article class="product-card">
                <img src="assets/images/wooden-cabinet.jpg"
                     alt="Vintage wooden cabinet">
                <div class="product-info">
                    <p class="product-category">FURNITURE</p>
                    <h3>Vintage Wooden Cabinet</h3>
                    <p class="product-price">$450.00</p>
                    <a href="products.php" class="product-button">Explore Collection</a>
                </div>
            </article>

            <article class="product-card">
                <img src="assets/images/table-lamp.jpg"
                     alt="Victorian table lamp">
                <div class="product-info">
                    <p class="product-category">LIGHTING</p>
                    <h3>Victorian Table Lamp</h3>
                    <p class="product-price">$180.00</p>
                    <a href="products.php" class="product-button">Explore Collection</a>
                </div>
            </article>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
