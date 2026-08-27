<?php
require dirname(__DIR__) . '/config/database.php';
require dirname(__DIR__) . '/includes/functions.php';

$aboutStats = [
    'products' => (int)$pdo->query("SELECT COUNT(*) FROM Product WHERE status='Active'")->fetchColumn(),
    'categories' => (int)$pdo->query('SELECT COUNT(*) FROM Category')->fetchColumn(),
    'customers' => (int)$pdo->query("SELECT COUNT(*) FROM Customer WHERE role='customer' AND status='Active'")->fetchColumn(),
];
$pageTitle = 'About Us';
require __DIR__ . '/partials/header.php';
?>
<section class="about-hero about-hero-banner"><div class="about-hero-content"><span class="about-banner-tag">GAMEGEAR HUB · MYANMAR</span><p class="eyebrow">ABOUT OUR STORE</p><h1>Where better gear meets <em>better play.</em></h1><p>We help players discover dependable gaming accessories, compare modern models, and confidently build a setup that matches how they play.</p><div class="hero-actions"><a class="button" href="products.php">Explore Products →</a><a class="about-outline-button" href="#what-we-offer">What We Offer ↓</a></div></div><div class="about-banner-note"><span>CURATED GAMING ACCESSORIES</span><strong>Play your way.</strong></div></section>

<section class="about-stats" aria-label="GameGear Hub statistics"><article><strong><?= $aboutStats['products'] ?>+</strong><span>Gaming products</span></article><article><strong><?= $aboutStats['categories'] ?></strong><span>Gear categories</span></article><article><strong><?= $aboutStats['customers'] ?>+</strong><span>Active customers</span></article><article><strong>24 hr</strong><span>Dispatch target</span></article></section>

<section class="about-story" id="our-story"><div class="about-story-image"><img src="https://images.unsplash.com/photo-1593305841991-05c297ba4575?auto=format&fit=crop&w=1200&q=85" alt="Gaming room with performance accessories"><span>GAMEGEAR HUB<br><small>YANGON · MYANMAR</small></span></div><div><p class="eyebrow">OUR STORY</p><h2>A local hub for every kind of player.</h2><p>GameGear Hub started with a simple idea: finding the right gaming accessory should be clear, convenient, and enjoyable. We bring keyboards, mice, headsets, controllers, cooling equipment, chairs, and other setup essentials together in one focused store.</p><p>Our catalog combines affordable everyday equipment with current premium models. Every listing is organized with practical specifications, transparent stock information, and customer feedback to help players choose confidently.</p></div></section>

<section class="about-mission"><div><p class="eyebrow">OUR MISSION</p><h2>Make better gaming gear easier to access.</h2></div><p>We aim to connect Myanmar players with quality products, fair pricing, secure checkout options, and responsive support—from the first search to final delivery.</p></section>

<section class="what-we-offer" id="what-we-offer"><div class="section-heading"><div><p class="eyebrow">WHAT WE OFFER</p><h2>Everything you need to upgrade.</h2><p>Products and services designed around a simpler gaming-gear experience.</p></div></div><div class="offer-grid">
    <article><span>⌨</span><div><small>01</small><h3>Gaming Accessories</h3><p>Keyboards, mice, headsets, controllers, RGB cooling, chairs, and complete setup essentials.</p><a href="products.php">Browse products →</a></div></article>
    <article><span>⌕</span><div><small>02</small><h3>Easy Product Discovery</h3><p>Search, categories, model specifications, stock information, and clear product details.</p><a href="products.php">Find your gear →</a></div></article>
    <article><span>％</span><div><small>03</small><h3>Special Promotions</h3><p>Featured products, new arrivals, flash sales, and checkout discount codes.</p><a href="index.php#offers">View offers →</a></div></article>
    <article><span>◇</span><div><small>04</small><h3>Secure Checkout</h3><p>Simple card, KBZPay, and WavePay demo payment options with order confirmation.</p><a href="cart.php">View your cart →</a></div></article>
    <article><span>⚡</span><div><small>05</small><h3>Fast Delivery</h3><p>Free delivery on qualifying orders and a target dispatch time of 24 hours.</p><a href="products.php">Start shopping →</a></div></article>
    <article><span>★</span><div><small>06</small><h3>Reviews & Support</h3><p>Customer ratings, comments, order history, personal profiles, and helpful assistance.</p><a href="products.php">Read reviews →</a></div></article>
</div></section>

<section class="about-values"><div class="section-heading"><div><p class="eyebrow">WHAT WE VALUE</p><h2>How we serve the gaming community.</h2></div></div><div class="about-value-grid"><article><span>✓</span><h3>Quality First</h3><p>We select products for performance, comfort, durability, and value.</p></article><article><span>◇</span><h3>Clear Shopping</h3><p>Useful specifications, honest stock status, and real customer reviews.</p></article><article><span>⚡</span><h3>Reliable Service</h3><p>Fast dispatch, simple payments, and support throughout each order.</p></article><article><span>◎</span><h3>Player Community</h3><p>Feedback from customers helps other players make better choices.</p></article></div></section>

<section class="about-cta"><div><p class="eyebrow">READY TO UPGRADE?</p><h2>Build a setup worth playing for.</h2><p>Explore the latest gaming accessories and find gear that fits your play style.</p></div><a class="button" href="products.php">Shop GameGear →</a></section>
<?php require __DIR__ . '/partials/footer.php'; ?>
