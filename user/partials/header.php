<?php $pageTitle = $pageTitle ?? 'GameGear Hub'; ?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | GameGear Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= appUrl('assets/css/style.css') ?>?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/css/style.css') ?>">
</head>
<body>
<div class="announcement"><span>Free delivery on orders over 200,000 MMK</span><span>7-day easy returns</span><span>Secure demo checkout</span></div>
<header class="site-header">
    <a class="brand" href="<?= appUrl('user/index.php') ?>"><span>GG</span> GameGear Hub</a>
    <button class="menu-button" type="button" aria-label="Open menu">☰</button>
    <nav>
        <a href="<?= appUrl('user/index.php') ?>">Home</a>
        <a href="<?= appUrl('user/products.php') ?>">Products</a>
        <a href="<?= appUrl('user/about.php') ?>">About Us</a>
        <a href="<?= appUrl('user/contact.php') ?>">Contact</a>
        <button class="cart-trigger link-button" type="button" aria-label="Open shopping cart">Cart <b class="badge" data-cart-count><?= cartCount() ?></b></button>
        <?php if (currentUser()): ?>
            <a href="<?= appUrl('user/orders.php') ?>">My orders</a>
            <a href="<?= appUrl('user/profile.php') ?>">Profile</a>
            <?php if (currentUser()['role'] === 'admin'): ?><a href="<?= appUrl('admin/index.php') ?>">Admin</a><?php endif; ?>
            <form action="<?= appUrl('actions/index.php') ?>" method="post" class="inline">
                <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                <input type="hidden" name="action" value="logout">
                <button class="link-button">Logout</button>
            </form>
        <?php else: ?>
            <a href="<?= appUrl('user/login.php') ?>">Login</a><a class="nav-cta" href="<?= appUrl('user/register.php') ?>">Register</a>
        <?php endif; ?>
    </nav>
</header>
<?php $miniCartItems=cartProducts($pdo); $miniCartTotal=array_sum(array_column($miniCartItems,'subtotal')); ?>
<div class="cart-backdrop" data-cart-close></div>
<aside class="cart-drawer" aria-hidden="true" aria-label="Shopping cart" data-cart-action="<?= appUrl('actions/index.php') ?>" data-cart-csrf="<?= csrfToken() ?>">
    <div class="drawer-head"><div><p class="eyebrow">YOUR LOADOUT</p><h2>Cart <span data-cart-count><?= cartCount() ?></span></h2></div><button class="drawer-close" type="button" data-cart-close aria-label="Close cart">×</button></div>
    <div class="drawer-items" data-cart-items>
        <?php if($miniCartItems): foreach($miniCartItems as $item): ?><article class="drawer-item"><img src="<?= e($item['image_url']) ?>" alt=""><div><strong><?= e($item['name']) ?></strong><small><?= $item['quantity'] ?> × <?= money($item['price']) ?></small></div></article><?php endforeach; else: ?><div class="drawer-empty"><span>⌁</span><p>Your cart is ready for an upgrade.</p></div><?php endif; ?>
    </div>
    <div class="drawer-footer"><div><span>Subtotal</span><strong data-cart-total><?= money($miniCartTotal) ?></strong></div><a class="button" href="<?= appUrl('user/cart.php') ?>">View cart & checkout</a><button class="secondary" type="button" data-cart-close>Continue shopping</button></div>
</aside>
<main class="container">
<?php if (!empty($_SESSION['flash'])): $notice = $_SESSION['flash']; unset($_SESSION['flash']); ?>
    <div class="alert <?= e($notice['type']) ?>"><?= e($notice['message']) ?></div>
<?php endif; ?>
