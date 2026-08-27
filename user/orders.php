<?php require dirname(__DIR__) . '/config/database.php';
require dirname(__DIR__) . '/includes/functions.php';
requireLogin();
$s = $pdo->prepare('SELECT o.orderID AS id,o.orderDate,(o.subtotal-o.discount) AS total_amount,o.discount,o.orderStatus AS status,o.created_at,p.paymentMethod AS payment_method,p.paymentStatus AS payment_status,p.transactionID AS transaction_id FROM `Order` o LEFT JOIN Payment p ON p.orderID=o.orderID WHERE o.customerID=? ORDER BY o.created_at DESC');
$s->execute([currentUser()['id']]);
$orders = $s->fetchAll();
$pageTitle = 'My Orders';
require __DIR__ . '/partials/header.php'; ?>
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
        <h2>No orders yet</h2><a class="button" href="products.php">Start shopping</a>
    </div><?php endif;
        require __DIR__ . '/partials/footer.php'; ?>
