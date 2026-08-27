<?php require dirname(__DIR__) . '/config/database.php';
require dirname(__DIR__) . '/includes/functions.php';
if (currentUser()) redirect('user/index.php');
$pageTitle = 'Login';
require __DIR__ . '/partials/header.php'; ?>
<section class="auth-card">
    <p class="eyebrow">WELCOME BACK</p>
    <h1>Log in</h1>
    <form action="<?= appUrl('actions/index.php') ?>" method="post">
        <input type="hidden" name="csrf" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="login">
        <label>Email<input type="email" name="email" required autocomplete="email"></label><label>Password<input type="password" name="password" required autocomplete="current-password"></label><button>Log in</button>
    </form>
    <p>New player? <a href="register.php">Create an account</a></p>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>