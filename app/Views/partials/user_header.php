<?php $pageTitle = $pageTitle ?? 'GameGear Hub'; ?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | GameGear Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=<?= filemtime(PUBLIC_PATH . '/assets/css/style.css') ?>">
</head>
<body>
<div class="announcement"><span>Free delivery on orders over 200,000 MMK</span><span>7-day easy returns</span><span>Secure demo checkout</span></div>
<header class="site-header">
    <a class="brand" href="<?= url('home') ?>"><span>GG</span> GameGear Hub</a>
    <button class="menu-button" type="button" aria-label="Open menu">☰</button>
    <nav>
        <a href="<?= url('home') ?>">Home</a>
        <a href="<?= url('products') ?>">Products</a>
        <a href="<?= url('about') ?>">About Us</a>
        <a href="<?= url('contact') ?>">Contact</a>
        <button class="cart-trigger link-button" type="button" aria-label="Open shopping cart">Cart <b class="badge" data-cart-count><?= $cartService->count() ?></b></button>
        <?php if (currentUser()): ?>
            <details class="account-menu">
                <summary>
                    <span class="account-avatar" aria-hidden="true"><?= e(strtoupper(substr(currentUser()['name'], 0, 1))) ?></span>
                    <span>Account</span>
                    <span class="account-chevron" aria-hidden="true">⌄</span>
                </summary>
                <div class="account-dropdown">
                    <div class="account-identity">
                        <strong><?= e(currentUser()['name']) ?></strong>
                        <small><?= currentUser()['role'] === 'admin' ? 'Administrator' : 'GameGear member' ?></small>
                    </div>
                    <a href="<?= url('orders') ?>">My orders <span aria-hidden="true">→</span></a>
                    <a href="<?= url('profile') ?>">Profile <span aria-hidden="true">→</span></a>
                    <?php if (currentUser()['role'] === 'admin'): ?><a href="<?= url('admin') ?>">Admin dashboard <span aria-hidden="true">→</span></a><?php endif; ?>
                    <form action="<?= url('action') ?>" method="post">
                        <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                        <input type="hidden" name="action" value="logout">
                        <button class="account-logout">Logout</button>
                    </form>
                </div>
            </details>
        <?php else: ?>
            <a href="<?= url('login') ?>">Login</a><a class="nav-cta" href="<?= url('register') ?>">Register</a>
        <?php endif; ?>
    </nav>
</header>
<?php $miniCartItems = $cartService->items(); $miniCartTotal = $cartService->total(); ?>
<div class="cart-backdrop" data-cart-close></div>
<aside class="cart-drawer" aria-hidden="true" aria-label="Shopping cart" data-cart-action="<?= url('action') ?>" data-cart-csrf="<?= csrfToken() ?>">
    <div class="drawer-head"><div><p class="eyebrow">YOUR LOADOUT</p><h2>Cart <span data-cart-count><?= $cartService->count() ?></span></h2></div><button class="drawer-close" type="button" data-cart-close aria-label="Close cart">×</button></div>
    <div class="drawer-items" data-cart-items>
        <?php if($miniCartItems): foreach($miniCartItems as $item): ?><article class="drawer-item"><img src="<?= e($item['image_url']) ?>" alt=""><div><strong><?= e($item['name']) ?></strong><small><?= $item['quantity'] ?> × <?= money($item['price']) ?></small></div></article><?php endforeach; else: ?><div class="drawer-empty"><span>⌁</span><p>Your cart is ready for an upgrade.</p></div><?php endif; ?>
    </div>
    <div class="drawer-footer"><div><span>Subtotal</span><strong data-cart-total><?= money($miniCartTotal) ?></strong></div><a class="button" href="<?= url('cart') ?>">View cart & checkout</a><button class="secondary" type="button" data-cart-close>Continue shopping</button></div>
</aside>
<main class="container">
<?php if (!empty($_SESSION['flash'])): $notice = $_SESSION['flash']; unset($_SESSION['flash']); ?>
    <div class="alert <?= e($notice['type']) ?>"><?= e($notice['message']) ?></div>
<?php endif; ?>
