<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$required = [
    'public/index.php',
    'public/.htaccess',
    'public/assets/css/style.css',
    'public/assets/js/app.js',
    'app/Controllers/AuthController.php',
    'app/Controllers/CartController.php',
    'app/Controllers/CheckoutController.php',
    'app/Controllers/AdminController.php',
    'app/Services/CartService.php',
    'app/Services/OrderService.php',
    'app/Views/user/home.php',
    'app/Views/admin/index.php',
    'app/Views/partials/user_header.php',
    'app/Support/helpers.php',
    'config/app.php',
    'config/database.php',
    'database/migrations/001_create_schema.sql',
    'database/seeds/001_demo_data.sql',
    '.env.example',
    'README.md',
];

$legacy = [
    'index.php', 'actions.php', 'admin.php', 'products.php', 'product.php',
    'cart.php', 'checkout.php', 'orders.php', 'login.php', 'register.php',
    'about.php', 'contact.php', 'user/actions.php', 'actions/index.php',
];

$failures = [];
foreach ($required as $path) {
    if (!is_file($root . '/' . $path)) {
        $failures[] = "Missing required file: {$path}";
    }
}
foreach ($legacy as $path) {
    if (file_exists($root . '/' . $path)) {
        $failures[] = "Legacy entry point still exists: {$path}";
    }
}

$migration = file_get_contents($root . '/database/migrations/001_create_schema.sql');
$seed = file_get_contents($root . '/database/seeds/001_demo_data.sql');
if (preg_match('/^\s*(INSERT|UPDATE|DELETE|REPLACE)\b/im', $migration)) {
    $failures[] = 'Schema migration contains seed data operations.';
}
if (preg_match('/^\s*(CREATE|ALTER|DROP|TRUNCATE)\b/im', $seed)) {
    $failures[] = 'Seed contains schema operations.';
}

if ($failures) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}

echo "Architecture test passed." . PHP_EOL;
