<?php
require dirname(__DIR__) . '/config/database.php';
require dirname(__DIR__) . '/includes/functions.php';

$categories = $pdo->query("SELECT c.categoryID AS id,c.categoryName AS name,(SELECT p.imageURL FROM Product p WHERE p.categoryID=c.categoryID AND p.status='Active' ORDER BY p.productID DESC LIMIT 1) AS image_url FROM Category c ORDER BY c.categoryID")->fetchAll();
$categoryLookup = [];
foreach ($categories as $category) $categoryLookup[strtolower($category['name'])] = $category;
$categoryDefinitions = [
    ['database' => 'gaming keyboards', 'name' => 'Gaming Keyboard', 'fallback' => 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?auto=format&fit=crop&w=900&q=80'],
    ['database' => 'gaming mice', 'name' => 'Gaming Mouse', 'fallback' => 'https://images.unsplash.com/photo-1527814050087-3793815479db?auto=format&fit=crop&w=900&q=80'],
    ['database' => 'headsets', 'name' => 'Headset', 'fallback' => 'https://images.unsplash.com/photo-1599669454699-248893623440?auto=format&fit=crop&w=900&q=80'],
    ['database' => 'controllers', 'name' => 'Controller', 'fallback' => 'https://images.unsplash.com/photo-1592840496694-26d035b52b48?auto=format&fit=crop&w=900&q=80'],
    ['database' => 'rgb cooling', 'name' => 'RGB Fan', 'fallback' => 'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?auto=format&fit=crop&w=900&q=80'],
    ['database' => 'gaming chairs', 'name' => 'Gaming Chair', 'fallback' => 'https://images.unsplash.com/photo-1598550476439-6847785fcea6?auto=format&fit=crop&w=900&q=80'],
];
$categoryCards = [];
foreach ($categoryDefinitions as $definition) {
    $stored = $categoryLookup[$definition['database']] ?? null;
    $categoryCards[] = ['id' => $stored['id'] ?? 0, 'name' => $definition['name'], 'image_url' => $stored['image_url'] ?? $definition['fallback']];
}

$productQuery = "SELECT p.productID AS id,p.productName AS name,p.price,p.stockQuantity AS stock,p.imageURL AS image_url,c.categoryName AS category_name,(SELECT COALESCE(SUM(op.quantity),0) FROM Order_product op WHERE op.productID=p.productID) AS sold,(SELECT COALESCE(AVG(r.rating),0) FROM Review r WHERE r.productID=p.productID) AS average_rating,(SELECT COUNT(*) FROM Review r WHERE r.productID=p.productID) AS review_count FROM Product p JOIN Category c ON c.categoryID=p.categoryID WHERE p.status='Active'";
$allProducts = $pdo->query($productQuery)->fetchAll();
$bestSellers = $allProducts;
usort($bestSellers, fn($a, $b) => [(int)$b['sold'], (int)$b['id']] <=> [(int)$a['sold'], (int)$a['id']]);
$newArrivals = $allProducts;
usort($newArrivals, fn($a, $b) => (int)$b['id'] <=> (int)$a['id']);
$popularProducts = $allProducts;
usort($popularProducts, fn($a, $b) => [(int)$b['review_count'], (float)$b['average_rating'], (int)$b['sold']] <=> [(int)$a['review_count'], (float)$a['average_rating'], (int)$a['sold']]);
$featuredGroups = ['Best Selling Products' => array_slice($bestSellers, 0, 3), 'New Arrivals' => array_slice($newArrivals, 0, 3), 'Popular Gaming Accessories' => array_slice($popularProducts, 0, 3)];
$promotionProducts = array_slice($bestSellers, 0, 3);

$reviews = $pdo->query("SELECT r.rating,r.comment,c.customerName AS customer_name,p.productName AS product_name FROM Review r JOIN Customer c ON c.customerID=r.customerID JOIN Product p ON p.productID=r.productID WHERE r.comment IS NOT NULL AND r.comment!='' ORDER BY r.updated_at DESC LIMIT 3")->fetchAll();
if (!$reviews) $reviews = [['rating' => 5, 'comment' => 'Excellent gaming mouse with fast response.', 'customer_name' => 'GameGear Customer', 'product_name' => 'Gaming Mouse']];

$pageTitle = 'Home';
require __DIR__ . '/partials/header.php';
?>
<section class="hero home-hero">
    <div class="hero-copy"><p class="eyebrow">UP TO 30% OFF GAMING ACCESSORIES</p><h1>Level Up Your <em>Gaming Experience.</em></h1><p>Upgrade your setup with responsive, reliable gaming accessories selected for serious players.</p><div class="hero-actions"><a class="button" href="products.php">Shop Now →</a><a class="text-link" href="#featured">View featured gear</a></div><div class="hero-proof"><div><strong>30% Off</strong><span>Special promotion</span></div><div><strong>24 hr</strong><span>Fast dispatch</span></div><div><strong>Secure</strong><span>Demo payment</span></div></div></div>
    <div class="hero-visual"><div class="hero-glow"></div><span class="hero-tag">SPECIAL OFFER</span><img src="https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=1200&q=85" alt="Gaming accessories promotion"><div class="floating-price"><small>LIMITED TIME</small><strong>Save up to 30%</strong></div></div>
</section>

<form class="home-search" action="products.php" method="get"><span>⌕</span><input type="search" name="search" aria-label="Search products" placeholder="Search keyboard, mouse, headset, controller..."><button>Search Products</button></form>

<section class="home-featured" id="featured"><div class="section-heading"><div><p class="eyebrow">FEATURED PRODUCTS</p><h2>Gear players are choosing.</h2></div><a class="text-link" href="products.php">View all products →</a></div>
    <?php foreach ($featuredGroups as $groupTitle => $groupProducts): ?><section class="featured-group"><div class="featured-title"><h3><?= e($groupTitle) ?></h3><span><?= count($groupProducts) ?> selected products</span></div><div class="product-grid home-product-grid">
        <?php foreach ($groupProducts as $product): ?><article class="product-card"><div class="product-image"><a href="product.php?id=<?= $product['id'] ?>"><img loading="lazy" src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>"></a><span class="product-tag"><?= (int)$product['stock'] > 10 ? 'IN STOCK' : 'LOW STOCK' ?></span></div><div class="product-body"><small><?= e($product['category_name']) ?></small><h3><a href="product.php?id=<?= $product['id'] ?>"><?= e($product['name']) ?></a></h3><div class="rating"><?= str_repeat('★', max(1, (int)round((float)$product['average_rating'] ?: 5))) ?> <span>(<?= (int)$product['review_count'] ?> reviews)</span></div><div class="price-row"><strong><?= money($product['price']) ?></strong><form action="<?= appUrl('actions/index.php') ?>" method="post" class="quick-add"><input type="hidden" name="csrf" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="add_cart"><input type="hidden" name="product_id" value="<?= $product['id'] ?>"><input type="hidden" name="quantity" value="1"><button <?= (int)$product['stock'] < 1 ? 'disabled' : '' ?>>Add to Cart</button></form></div></div></article><?php endforeach; ?>
    </div></section><?php endforeach; ?>
</section>

<section class="home-categories" id="categories"><div class="section-heading"><div><p class="eyebrow">SHOP BY CATEGORY</p><h2>Complete your setup.</h2></div></div><div class="category-image-grid">
    <?php foreach ($categoryCards as $category): ?><a href="products.php?<?= $category['id'] ? 'category='.$category['id'] : 'search='.urlencode($category['name']) ?>"><img loading="lazy" src="<?= e($category['image_url']) ?>" alt="<?= e($category['name']) ?>"><span><strong><?= e($category['name']) ?></strong><small>Shop category →</small></span></a><?php endforeach; ?>
</div></section>

<section class="promotion-section" id="offers"><div class="section-heading"><div><p class="eyebrow">SPECIAL OFFERS · LIMITED TIME</p><h2>Promotion products.</h2><p class="promotion-note">Use code <strong>FLASH30</strong> at checkout to receive the displayed 30% discount.</p></div><a class="text-link" href="products.php">Browse all gear →</a></div><div class="promotion-product-grid">
    <?php foreach($promotionProducts as $product): $salePrice=(float)$product['price']*.70; ?><article class="promotion-product"><div class="promotion-image"><a href="product.php?id=<?= $product['id'] ?>"><img loading="lazy" src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>"></a><span>30% OFF</span></div><div class="promotion-body"><small>⚡ FLASH SALE</small><h3><a href="product.php?id=<?= $product['id'] ?>"><?= e($product['name']) ?></a></h3><div class="promotion-price"><del><?= money($product['price']) ?></del><strong><?= money($salePrice) ?></strong></div><form action="<?= appUrl('actions/index.php') ?>" method="post" class="quick-add"><input type="hidden" name="csrf" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="add_cart"><input type="hidden" name="product_id" value="<?= $product['id'] ?>"><input type="hidden" name="quantity" value="1"><button <?= (int)$product['stock']<1?'disabled':'' ?>>Add to Cart</button></form></div></article><?php endforeach; ?>
</div></section>

<section class="customer-reviews"><div class="section-heading"><div><p class="eyebrow">CUSTOMER REVIEWS</p><h2>Trusted by players.</h2></div><a class="text-link" href="products.php">Review a product →</a></div><div class="testimonial-grid">
    <?php foreach ($reviews as $review): ?><article><div class="review-stars"><?= str_repeat('★', (int)$review['rating']).str_repeat('☆', 5-(int)$review['rating']) ?></div><blockquote>“<?= e($review['comment']) ?>”</blockquote><strong><?= e($review['customer_name']) ?></strong><small><?= e($review['product_name']) ?></small></article><?php endforeach; ?>
</div></section>

<section class="why-us"><div class="section-heading"><div><p class="eyebrow">WHY CHOOSE US?</p><h2>Game-ready service.</h2></div></div><div class="why-grid"><article><span>⚡</span><h3>Fast Delivery</h3><p>Carefully packed and dispatched within 24 hours.</p></article><article><span>◇</span><h3>Secure Payment</h3><p>Simple card and mobile demo payment options.</p></article><article><span>✓</span><h3>Quality Products</h3><p>Gaming gear selected for comfort and performance.</p></article><article><span>◎</span><h3>Customer Support</h3><p>Helpful support throughout your shopping journey.</p></article></div></section>
<?php require __DIR__ . '/partials/footer.php'; ?>
