<?php require APP_PATH . '/Views/partials/user_header.php'; ?>
<section class="auth-card">
    <p class="eyebrow">WELCOME BACK</p>
    <h1>Log in</h1>
    <form action="<?= url('action') ?>" method="post">
        <input type="hidden" name="csrf" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="login">
        <label>Email<input type="email" name="email" required autocomplete="email"></label><label>Password<input type="password" name="password" required autocomplete="current-password"></label><button>Log in</button>
    </form>
    <p>New player? <a href="<?= url('register') ?>">Create an account</a></p>
</section>
<?php require APP_PATH . '/Views/partials/user_footer.php'; ?>
