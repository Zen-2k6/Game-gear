<?php
declare(strict_types=1);

putenv('APP_URL=/gamegear/public');
require dirname(__DIR__) . '/config/app.php';
require APP_PATH . '/Support/helpers.php';

$cartService = new class {
    public function items(): array { return []; }
    public function total(): float { return 0; }
    public function count(): int { return 0; }
};

$_SESSION['user'] = ['id' => 1, 'name' => 'Test Admin', 'email' => 'admin@example.test', 'phone' => '09111111111', 'address' => 'Yangon', 'role' => 'admin'];

$product = ['id' => 1, 'category_id' => 1, 'name' => 'Test Keyboard', 'description' => 'Description', 'specifications' => 'Specifications', 'price' => 100000, 'stock' => 5, 'image_url' => 'https://example.test/product.png', 'category_name' => 'Keyboards', 'average_rating' => 4.5, 'review_count' => 2, 'sold' => 3];
$review = ['reviewID' => 1, 'rating' => 5, 'comment' => 'Excellent', 'created_at' => '2026-01-01 00:00:00', 'customer_name' => 'Player One', 'customerID' => 2];
$order = ['id' => 1, 'total_amount' => 100000, 'discount' => 0, 'status' => 'Processing', 'created_at' => '2026-01-01 00:00:00', 'payment_method' => 'KBZPay Mobile', 'payment_status' => 'Paid', 'customer_name' => 'Player One'];
$category = ['id' => 1, 'name' => 'Keyboards'];

$userViews = [
    ['user/home', ['pageTitle' => 'Home', 'cartService' => $cartService, 'categoryCards' => [['id' => 1, 'name' => 'Keyboards', 'image_url' => $product['image_url']]], 'featuredGroups' => ['Featured' => [$product]], 'promotionProducts' => [$product], 'reviews' => [$review + ['product_name' => 'Test Keyboard']]]],
    ['user/products', ['pageTitle' => 'Products', 'cartService' => $cartService, 'search' => '', 'categoryId' => 0, 'categories' => [$category], 'products' => [$product]]],
    ['user/product', ['pageTitle' => 'Product', 'cartService' => $cartService, 'product' => $product, 'reviews' => [$review], 'ratingCount' => 1, 'averageRating' => 5.0, 'myReview' => null]],
    ['user/cart', ['pageTitle' => 'Cart', 'cartService' => $cartService, 'items' => [], 'total' => 0.0]],
    ['user/checkout', ['pageTitle' => 'Checkout', 'cartService' => $cartService, 'items' => [$product + ['quantity' => 1, 'subtotal' => 100000]], 'total' => 100000.0]],
    ['user/orders', ['pageTitle' => 'Orders', 'cartService' => $cartService, 'orders' => [$order]]],
    ['user/profile', ['pageTitle' => 'Profile', 'cartService' => $cartService, 'profile' => ['name' => 'Test Admin', 'email' => 'admin@example.test', 'phone' => '09111111111', 'address' => 'Yangon', 'created_at' => '2026-01-01 00:00:00']]],
    ['user/login', ['pageTitle' => 'Login', 'cartService' => $cartService]],
    ['user/register', ['pageTitle' => 'Register', 'cartService' => $cartService]],
    ['user/about', ['pageTitle' => 'About', 'cartService' => $cartService, 'aboutStats' => ['products' => 1, 'categories' => 1, 'customers' => 1]]],
    ['user/contact', ['pageTitle' => 'Contact', 'cartService' => $cartService]],
];

foreach ($userViews as [$view, $data]) {
    ob_start();
    render($view, $data);
    $html = ob_get_clean();
    if (!str_contains($html, '<html')) {
        fwrite(STDERR, "View did not render a document: {$view}" . PHP_EOL);
        exit(1);
    }
    foreach (['account-menu', 'My orders', 'Profile', 'Admin dashboard'] as $accountMenuContent) {
        if (!str_contains($html, $accountMenuContent)) {
            fwrite(STDERR, "Account menu did not render on view: {$view}" . PHP_EOL);
            exit(1);
        }
    }
}

$adminData = [
    'categories' => [$category], 'products' => [$product], 'orders' => [$order],
    'customers' => [['id' => 2, 'name' => 'Player One', 'email' => 'player@example.test', 'phone' => '09222222222', 'address' => 'Yangon', 'status' => 'Active', 'created_at' => '2026-01-01 00:00:00']],
    'feedback' => [$review + ['id' => 1, 'email' => 'player@example.test', 'product_id' => 1, 'product_name' => 'Test Keyboard', 'image_url' => $product['image_url']]],
    'averageFeedback' => 5.0, 'stats' => ['products' => 1, 'categories' => 1, 'orders' => 1, 'customers' => 1, 'sales' => 100000],
    'recentOrders' => [$order], 'lowStock' => [$product], 'editProduct' => null, 'editCategory' => null,
    'orderStatusReport' => [['label' => 'Processing', 'total' => 1]], 'categorySalesReport' => [['label' => 'Keyboards', 'total' => 100000]],
    'monthlySalesReport' => [['label' => '2026-01', 'total' => 100000]], 'maxMonthlySales' => 100000.0,
    'titles' => ['dashboard' => 'Dashboard', 'products' => 'Products & Stock', 'categories' => 'Categories', 'orders' => 'Orders', 'customers' => 'Customers', 'feedback' => 'Customer Feedback', 'reports' => 'Reports & Analysis'],
];

foreach (array_keys($adminData['titles']) as $tab) {
    ob_start();
    render('admin/index', $adminData + ['tab' => $tab, 'pageTitle' => $adminData['titles'][$tab] . ' · Admin']);
    $html = ob_get_clean();
    if (!str_contains($html, 'admin-app')) {
        fwrite(STDERR, "Admin tab did not render: {$tab}" . PHP_EOL);
        exit(1);
    }
}

echo "View render test passed." . PHP_EOL;
