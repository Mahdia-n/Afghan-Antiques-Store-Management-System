
<?php
$pageTitle = 'Antique Store | Timeless Treasures';

require __DIR__ . '/includes/header.php';
?>

<main>
    <!-- Hero section -->
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
                Purchase Now <span aria-hidden="true">→</span>
            </a>
        </div>

        <div class="hero-caption">
            TIMELESS DESIGN · EXCEPTIONAL CRAFTSMANSHIP
        </div>
    </section>

    <!-- Featured products -->
    <section class="featured-section">
        <div class="section-heading">
            <p class="eyebrow">HANDPICKED FOR YOU</p>
            <h2>Featured Antiques</h2>
            <p>Bring history, character, and elegance into your home.</p>
        </div>

        <div class="product-grid">
            <article class="product-card">
                <div class="product-image product-clock"></div>
                <div class="product-info">
                    <p class="product-category">TIMEPIECES</p>
                    <h3>Antique Mantel Clock</h3>
                    <p class="product-price">$120.00</p>
                    <a href="products.php" class="product-button">
                        Explore Collection
                    </a>
                </div>
            </article>

            <article class="product-card">
                <div class="product-image product-vase"></div>
                <div class="product-info">
                    <p class="product-category">CERAMICS</p>
                    <h3>Classic Ceramic Vase</h3>
                    <p class="product-price">$95.00</p>
                    <a href="products.php" class="product-button">
                        Explore Collection
                    </a>
                </div>
            </article>

            <article class="product-card">
                <div class="product-image product-cabinet"></div>
                <div class="product-info">
                    <p class="product-category">FURNITURE</p>
                    <h3>Vintage Wooden Cabinet</h3>
                    <p class="product-price">$450.00</p>
                    <a href="products.php" class="product-button">
                        Explore Collection
                    </a>
                </div>
            </article>

            <article class="product-card">
                <div class="product-image product-lamp"></div>
                <div class="product-info">
                    <p class="product-category">LIGHTING</p>
                    <h3>Victorian Table Lamp</h3>
                    <p class="product-price">$180.00</p>
                    <a href="products.php" class="product-button">
                        Explore Collection
                    </a>
                </div>
            </article>
        </div>

        <div class="collection-link">
            <a href="products.php" class="primary-button">
                View All Antiques →
            </a>
        </div>
    </section>

    <!-- Store introduction -->
    <section class="story-section">
        <div>
            <p class="eyebrow">MORE THAN OBJECTS</p>
            <h2>Every Antique Has a Story</h2>
        </div>

        <p>
            From beautifully crafted furniture to timeless decorative pieces,
            discover antiques that bring warmth, history, and individuality
            to your home.
        </p>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
