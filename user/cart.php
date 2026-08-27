<?php require dirname(__DIR__) . '/config/database.php';
require dirname(__DIR__) . '/includes/functions.php';
$items = cartProducts($pdo);
$total = array_sum(array_column($items, 'subtotal'));
$pageTitle = 'Shopping Cart';
require __DIR__ . '/partials/header.php'; ?>
<div class="section-heading">
    <div>
        <p class="eyebrow">YOUR LOADOUT</p>
        <h1>Shopping cart</h1>
    </div>
</div>
<?php if ($items): ?><form action="<?= appUrl('actions/index.php') ?>" method="post"><input type="hidden" name="csrf" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="update_cart">
        <div class="cart-layout">
            <section class="cart-list"><?php foreach ($items as $item): ?><article class="cart-item"><img src="<?= e($item['image_url']) ?>" alt="">
                        <div>
                            <h3><?= e($item['name']) ?></h3>
                            <p><?= money($item['price']) ?> each</p>
                        </div><label>Qty<div class="quantity-control"><button type="button" data-quantity-change="-1" aria-label="Decrease quantity">−</button><input type="number" name="quantities[<?= $item['id'] ?>]" min="0" max="<?= $item['stock'] ?>" value="<?= $item['quantity'] ?>"><button type="button" data-quantity-change="1" aria-label="Increase quantity">+</button></div><button class="remove-cart-item" type="button" data-remove-cart>Remove</button></label><strong><?= money($item['subtotal']) ?></strong>
                    </article><?php endforeach; ?><button class="secondary">Update cart</button></section>
            <aside class="summary">
                <h2>Order summary</h2>
                <div><span>Subtotal</span><strong><?= money($total) ?></strong></div>
                <div><span>Delivery</span><strong>Free</strong></div>
                <hr>
                <div class="total"><span>Total</span><strong><?= money($total) ?></strong></div><a class="button" href="checkout.php">Proceed to checkout</a>
            </aside>
        </div>
    </form>
<?php else: ?><div class="empty">
        <h2>Your cart is empty</h2>
        <p>Add some gear to start your order.</p><a class="button" href="products.php">Browse products</a>
    </div><?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
