<?php require dirname(__DIR__) . '/config/database.php'; require dirname(__DIR__) . '/includes/functions.php'; if(currentUser()) redirect('user/index.php'); $pageTitle='Register'; require __DIR__ . '/partials/header.php'; ?>
<section class="auth-card"><p class="eyebrow">JOIN THE HUB</p><h1>Create account</h1><form action="<?= appUrl('actions/index.php') ?>" method="post">
<input type="hidden" name="csrf" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="register">
<label>Full name<input name="name" required maxlength="100"></label><label>Email<input type="email" name="email" required></label><label>Phone number<input name="phone" required maxlength="30"></label><label>Address<textarea name="address" rows="3" placeholder="Optional default delivery address"></textarea></label><label>Password<input type="password" name="password" required minlength="8"></label><button>Register</button></form></section>
<?php require __DIR__ . '/partials/footer.php'; ?>
