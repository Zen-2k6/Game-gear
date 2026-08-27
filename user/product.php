<?php
require dirname(__DIR__) . '/config/database.php'; require dirname(__DIR__) . '/includes/functions.php';
$statement = $pdo->prepare("SELECT p.productID AS id,p.categoryID AS category_id,p.productName AS name,p.description,p.specifications,p.price,p.stockQuantity AS stock,p.imageURL AS image_url,c.categoryName AS category_name FROM Product p JOIN Category c ON c.categoryID=p.categoryID WHERE p.productID=? AND p.status='Active'");
$statement->execute([(int)($_GET['id'] ?? 0)]); $product = $statement->fetch();
if (!$product) { http_response_code(404); exit('Product not found.'); }
$reviewStatement = $pdo->prepare('SELECT r.reviewID,r.rating,r.comment,r.created_at,c.customerName AS customer_name,r.customerID FROM Review r JOIN Customer c ON c.customerID=r.customerID WHERE r.productID=? ORDER BY r.updated_at DESC');
$reviewStatement->execute([$product['id']]);
$reviews = $reviewStatement->fetchAll();
$ratingCount = count($reviews);
$averageRating = $ratingCount ? array_sum(array_column($reviews, 'rating')) / $ratingCount : 0;
$myReview = null;
if (currentUser()) foreach ($reviews as $review) if ((int)$review['customerID'] === (int)currentUser()['id']) $myReview = $review;
$pageTitle = $product['name']; require __DIR__ . '/partials/header.php';
?>
<a class="back" href="products.php">← Back to products</a>
<section class="product-detail">
    <img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>">
    <div><p class="eyebrow"><?= e($product['category_name']) ?></p><h1><?= e($product['name']) ?></h1><p class="detail-price"><?= money($product['price']) ?></p>
        <p><?= nl2br(e($product['description'])) ?></p><div class="spec"><strong>Specifications</strong><p><?= nl2br(e($product['specifications'])) ?></p></div>
        <p class="stock"><?= (int)$product['stock'] > 0 ? $product['stock'].' available' : 'Out of stock' ?></p>
        <form action="<?= appUrl('actions/index.php') ?>" method="post" class="add-form ajax-cart-form">
            <input type="hidden" name="csrf" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="add_cart"><input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <input type="number" name="quantity" min="1" max="<?= $product['stock'] ?>" value="1"><button <?= $product['stock'] < 1 ? 'disabled' : '' ?>>Add to cart</button>
        </form>
        <a class="product-review-link" href="#reviews"><?= currentUser() ? ($myReview ? 'Update your rating & comment' : 'Write a rating & comment') : 'View ratings & reviews' ?> ↓</a>
    </div>
</section>
<section class="reviews-section" id="reviews">
    <div class="reviews-heading"><div><p class="eyebrow">CUSTOMER FEEDBACK</p><h2>Ratings & reviews</h2></div><div class="rating-summary"><strong><?= $ratingCount ? number_format($averageRating, 1) : '—' ?></strong><span><?= $ratingCount ? str_repeat('★', (int)round($averageRating)).str_repeat('☆', 5-(int)round($averageRating)) : 'No ratings yet' ?></span><small><?= $ratingCount ?> review<?= $ratingCount === 1 ? '' : 's' ?></small></div></div>
    <div class="reviews-layout">
        <div class="review-list">
            <?php foreach ($reviews as $review): ?><article><div><strong><?= e($review['customer_name']) ?></strong><span class="review-stars"><?= str_repeat('★', (int)$review['rating']).str_repeat('☆', 5-(int)$review['rating']) ?></span></div><p><?= nl2br(e($review['comment'])) ?></p><small><?= date('F j, Y', strtotime($review['created_at'])) ?></small></article><?php endforeach; ?>
            <?php if (!$reviews): ?><div class="empty review-empty"><h3>Be the first to review</h3><p>Share your experience with this product.</p></div><?php endif; ?>
        </div>
        <aside class="form-panel review-form">
            <?php if (currentUser()): ?><h3><?= $myReview ? 'Update your review' : 'Write a review' ?></h3><form action="<?= appUrl('actions/index.php') ?>" method="post"><input type="hidden" name="csrf" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="add_review"><input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <label>Rating<select name="rating" required><option value="">Choose 1–5 stars</option><?php for($rating=5;$rating>=1;$rating--): ?><option value="<?= $rating ?>" <?= (int)($myReview['rating'] ?? 0) === $rating ? 'selected' : '' ?>><?= $rating ?> star<?= $rating === 1 ? '' : 's' ?></option><?php endfor; ?></select></label>
                <label>Comment<textarea name="comment" rows="5" maxlength="1500" required placeholder="What did you like about this product?"><?= e($myReview['comment'] ?? '') ?></textarea></label><button><?= $myReview ? 'Update review' : 'Submit review' ?></button></form>
            <?php else: ?><h3>Purchased this gear?</h3><p>Log in to leave a rating and comment.</p><a class="button" href="login.php">Log in to review</a><?php endif; ?>
        </aside>
    </div>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
