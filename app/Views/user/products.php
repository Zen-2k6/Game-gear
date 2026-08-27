<?php require APP_PATH . '/Views/partials/user_header.php'; ?>
<div class="catalog-hero"><p class="eyebrow">GAMEGEAR CATALOG</p><h1>Find your next upgrade.</h1><p>Browse performance gaming gear for every part of your setup.</p></div>
<section id="shop">
    <form class="filters" method="get" action="<?= url('home') ?>"><input type="hidden" name="route" value="products">
        <input type="search" name="search" value="<?= e($search) ?>" placeholder="Search products or categories...">
        <select name="category"><option value="0">All categories</option><?php foreach ($categories as $category): ?><option value="<?= $category['id'] ?>" <?= $categoryId === (int)$category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select>
        <button>Find gear</button>
    </form>
    <div class="section-heading"><div><p class="eyebrow">CURATED FOR PLAYERS</p><h2><?= $search || $categoryId ? 'Your results' : 'All products' ?></h2></div><span><?= count($products) ?> products</span></div>
    <section class="product-grid">
        <?php foreach ($products as $product): ?><article class="product-card">
            <div class="product-image"><a href="<?= url('product', ['id' => $product['id']]) ?>"><img loading="lazy" src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>"></a><span class="product-tag"><?= $product['stock'] > 10 ? 'IN STOCK' : 'LOW STOCK' ?></span></div>
            <div class="product-body"><small><?= e($product['category_name']) ?></small><h3><a href="<?= url('product', ['id' => $product['id']]) ?>"><?= e($product['name']) ?></a></h3><div class="rating" aria-label="Rated <?= number_format((float)$product['average_rating'], 1) ?> out of 5"><?= $product['review_count'] ? str_repeat('★', max(1, (int)round((float)$product['average_rating']))) : 'No ratings' ?> <span>(<?= (int)$product['review_count'] ?> reviews)</span></div><div class="price-row"><strong><?= money($product['price']) ?></strong><form action="<?= url('action') ?>" method="post" class="quick-add"><input type="hidden" name="csrf" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="add_cart"><input type="hidden" name="product_id" value="<?= $product['id'] ?>"><input type="hidden" name="quantity" value="1"><button <?= $product['stock'] < 1 ? 'disabled' : '' ?> aria-label="Add <?= e($product['name']) ?> to cart">+</button></form></div></div>
        </article><?php endforeach; ?>
    </section>
</section>
<?php if (!$products): ?><div class="empty"><h2>No gear found</h2><p>Try another search or category.</p><a class="button" href="<?= url('products') ?>">View all products</a></div><?php endif; ?>
<?php require APP_PATH . '/Views/partials/user_footer.php'; ?>
