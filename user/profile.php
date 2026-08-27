<?php
require dirname(__DIR__) . '/config/database.php';
require dirname(__DIR__) . '/includes/functions.php';
requireLogin();
$statement = $pdo->prepare('SELECT customerName AS name,email,phone,address,created_at FROM Customer WHERE customerID=?');
$statement->execute([currentUser()['id']]);
$profile = $statement->fetch();
$pageTitle = 'My Profile';
require __DIR__ . '/partials/header.php';
?>
<div class="section-heading"><div><p class="eyebrow">PERSONAL INFO</p><h1>My profile</h1></div></div>
<section class="profile-layout">
    <article class="profile-summary"><span><?= e(strtoupper(substr($profile['name'], 0, 1))) ?></span><h2><?= e($profile['name']) ?></h2><p><?= e($profile['email']) ?></p><small>Member since <?= date('F Y', strtotime($profile['created_at'])) ?></small><a href="orders.php">View order history →</a></article>
    <article class="form-panel"><h2>Account details</h2><p class="muted">Keep your delivery and contact details up to date.</p>
        <form action="<?= appUrl('actions/index.php') ?>" method="post"><input type="hidden" name="csrf" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="update_profile">
            <label>Full name<input name="name" required maxlength="100" value="<?= e($profile['name']) ?>"></label>
            <label>Email<input type="email" name="email" required maxlength="190" value="<?= e($profile['email']) ?>"></label>
            <label>Phone number<input name="phone" required maxlength="30" value="<?= e($profile['phone']) ?>"></label>
            <label>Default delivery address<textarea name="address" rows="4" placeholder="Street, township, city"><?= e($profile['address']) ?></textarea></label>
            <label>New password <small>(leave blank to keep the current password)</small><input type="password" name="password" minlength="8" autocomplete="new-password"></label>
            <button>Save profile</button>
        </form>
    </article>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
