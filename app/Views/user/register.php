<?php require APP_PATH . '/Views/partials/user_header.php'; ?>
<section class="auth-card"><p class="eyebrow">JOIN THE HUB</p><h1>Create account</h1><form action="<?= url('action') ?>" method="post">
<input type="hidden" name="csrf" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="register">
<label>Full name<input name="name" required maxlength="100"></label><label>Email<input type="email" name="email" required></label><label>Phone number<input name="phone" required maxlength="30"></label><label>Address<textarea name="address" rows="3" placeholder="Optional default delivery address"></textarea></label><label>Password<input type="password" name="password" required minlength="8"></label><button>Register</button></form></section>
<?php require APP_PATH . '/Views/partials/user_footer.php'; ?>
