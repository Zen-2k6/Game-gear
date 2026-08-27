<?php
require dirname(__DIR__) . '/config/database.php';
require dirname(__DIR__) . '/includes/functions.php';

$search = trim($_GET['search'] ?? '');
$categoryId = (int)($_GET['category'] ?? 0);
$categories = $pdo->query('SELECT categoryID AS id,categoryName AS name FROM Category ORDER BY categoryName')->fetchAll();
$sql = "SELECT p.productID AS id,p.categoryID AS category_id,p.productName AS name,p.description,p.specifications,p.price,p.stockQuantity AS stock,p.imageURL AS image_url,c.categoryName AS category_name FROM Product p JOIN Category c ON c.categoryID=p.categoryID WHERE p.status='Active'";
$values = [];
if ($search !== '') {
    $sql .= ' AND (p.productName LIKE ? OR p.description LIKE ? OR c.categoryName LIKE ?)';
    $term = "%$search%";
    array_push($values, $term, $term, $term);
}
if ($categoryId > 0) {
    $sql .= ' AND p.categoryID=?';
    $values[] = $categoryId;
}
$sql .= ' ORDER BY p.created_at DESC';
$statement = $pdo->prepare($sql);
$statement->execute($values);
$products = $statement->fetchAll();
$pageTitle = 'Products';
require __DIR__ . '/partials/header.php';
?>
<div class="catalog-hero"><p class="eyebrow">GAMEGEAR CATALOG</p><h1>Find your next upgrade.</h1><p>Browse performance gaming gear for every part of your setup.</p></div>
<section id="shop">
    <form class="filters" method="get" action="products.php">
        <input type="search" name="search" value="<?= e($search) ?>" placeholder="Search products or categories...">
        <select name="category"><option value="0">All categories</option><?php foreach ($categories as $category): ?><option value="<?= $category['id'] ?>" <?= $categoryId === (int)$category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select>
        <button>Find gear</button>
    </form>
    <div class="section-heading"><div><p class="eyebrow">CURATED FOR PLAYERS</p><h2><?= $search || $categoryId ? 'Your results' : 'All products' ?></h2></div><span><?= count($products) ?> products</span></div>
    <section class="product-grid">
        <?php foreach ($products as $product): ?><article class="product-card">
            <div class="product-image"><a href="product.php?id=<?= $product['id'] ?>"><img loading="lazy" src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>"></a><span class="product-tag"><?= $product['stock'] > 10 ? 'IN STOCK' : 'LOW STOCK' ?></span></div>
            <div class="product-body"><small><?= e($product['category_name']) ?></small><h3><a href="product.php?id=<?= $product['id'] ?>"><?= e($product['name']) ?></a></h3><div class="rating" aria-label="Rated 5 out of 5">★★★★★ <span>(<?= 18 + $product['id'] * 7 ?>)</span></div><div class="price-row"><strong><?= money($product['price']) ?></strong><form action="<?= appUrl('actions/index.php') ?>" method="post" class="quick-add"><input type="hidden" name="csrf" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="add_cart"><input type="hidden" name="product_id" value="<?= $product['id'] ?>"><input type="hidden" name="quantity" value="1"><button <?= $product['stock'] < 1 ? 'disabled' : '' ?> aria-label="Add <?= e($product['name']) ?> to cart">+</button></form></div></div>
        </article><?php endforeach; ?>
    </section>
</section>
<?php if (!$products): ?><div class="empty"><h2>No gear found</h2><p>Try another search or category.</p><a class="button" href="products.php">View all products</a></div><?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
