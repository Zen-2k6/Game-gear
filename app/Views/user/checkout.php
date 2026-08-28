<?php require APP_PATH . '/Views/partials/user_header.php'; ?>
<div class="section-heading">
    <div>
        <p class="eyebrow">SECURE CHECKOUT</p>
        <h1>Delivery & payment</h1>
    </div>
</div>
<form class="checkout" action="<?= url('action') ?>" method="post"><input type="hidden" name="csrf" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="checkout">
    <section class="form-panel">
        <h2>Delivery information</h2><label>Receiver name<input name="receiver_name" required value="<?= e(currentUser()['name']) ?>"></label><label>Phone number<input name="phone" required value="<?= e(currentUser()['phone']) ?>"></label><label>Delivery address<textarea name="address" required rows="4" placeholder="Street, township, city"><?= e(currentUser()['address'] ?? '') ?></textarea></label>
        <h2>Online payment</h2><label>Payment method<select name="payment_method" required>
                <option value="Credit Card Demo">Credit / debit card (Demo)</option>
                <option value="KBZPay Mobile">KBZPay mobile app (Demo)</option>
                <option value="WavePay Mobile">WavePay mobile app (Demo)</option>
            </select></label>
        <label>Discount code<input name="discount_code" maxlength="20" placeholder="e.g. FLASH30"></label><p class="discount-hint">Use <strong>FLASH30</strong> for 30% off, <strong>GAME10</strong> for 10%, or <strong>WELCOME5</strong> for 5%.</p>
        <p class="notice">Academic demo: payment succeeds without collecting or storing real financial details.</p>
    </section>
    <aside class="summary">
        <h2><?= count($items) ?> item(s)</h2><?php foreach ($items as $item): ?><div><span><?= e($item['name']) ?> × <?= $item['quantity'] ?></span><strong><?= money($item['subtotal']) ?></strong></div><?php endforeach; ?>
        <hr>
        <div class="total"><span>Total</span><strong><?= money($total) ?></strong></div><button>Pay & place order</button>
    </aside>
</form>
<?php require APP_PATH . '/Views/partials/user_footer.php'; ?>
