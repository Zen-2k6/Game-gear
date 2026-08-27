<?php require APP_PATH . '/Views/partials/user_header.php'; ?>
<div class="section-heading">
    <div>
        <p class="eyebrow">PURCHASES</p>
        <h1>My orders</h1>
    </div>
</div>
<section class="order-list"><?php foreach ($orders as $order): ?><article>
            <div><strong>Order #<?= $order['id'] ?></strong><small><?= e($order['created_at']) ?></small></div><span class="status"><?= e($order['status']) ?></span>
            <div><small><?= e($order['payment_method'] ?: 'Payment') ?></small><strong><?= e($order['payment_status']) ?></strong></div>
            <div><small>Total<?= (float)$order['discount'] > 0 ? ' · discount applied' : '' ?></small><strong><?= money($order['total_amount']) ?></strong></div>
        </article><?php endforeach; ?></section>
<?php if (!$orders): ?><div class="empty">
        <h2>No orders yet</h2><a class="button" href="<?= url('products') ?>">Start shopping</a>
    </div><?php endif; ?>
<?php require APP_PATH . '/Views/partials/user_footer.php'; ?>
