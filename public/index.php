<?php
declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\CheckoutController;
use App\Controllers\StorefrontController;
use App\Services\CartService;
use App\Services\OrderService;

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$requestPath = '/' . trim($requestPath, '/');
$requestPath = $requestPath === '/' ? '/' : rtrim($requestPath, '/');

if (PHP_SAPI === 'cli-server') {
    $publicRoot = realpath(__DIR__);
    $requestedFile = realpath(__DIR__ . $requestPath);
    $isPublicFile = $publicRoot !== false
        && $requestedFile !== false
        && str_starts_with($requestedFile, $publicRoot . DIRECTORY_SEPARATOR)
        && is_file($requestedFile);

    if ($requestPath !== '/index.php' && $isPublicFile) {
        return false;
    }
}

if (!isset($_GET['route'])) {
    $_GET['route'] = match ($requestPath) {
        '/', '/index.php' => 'home',
        '/admin' => 'admin',
        default => '__not_found__',
    };
}

require dirname(__DIR__) . '/config/app.php';
require APP_PATH . '/Support/helpers.php';
require ROOT_PATH . '/config/database.php';

$cartService = new CartService($pdo);
$orderService = new OrderService($pdo);
$authController = new AuthController($pdo, $cartService);
$cartController = new CartController($cartService);
$checkoutController = new CheckoutController($cartService, $orderService);
$storefrontController = new StorefrontController($pdo, $cartService);
$adminController = new AdminController($pdo);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    verifyCsrf();
    $action = (string) ($_POST['action'] ?? '');

    match ($action) {
        'register' => $authController->register($_POST),
        'login' => $authController->login($_POST),
        'logout' => $authController->logout(),
        'update_profile' => $authController->updateProfile($_POST),
        'add_cart' => $cartController->add($_POST),
        'update_cart' => $cartController->update($_POST),
        'checkout' => $checkoutController->place($_POST),
        'add_review' => $storefrontController->review($_POST),
        default => str_starts_with($action, 'admin_')
            ? $adminController->handle($action, $_POST)
            : redirectTo('home'),
    };
}

$route = (string) ($_GET['route'] ?? 'home');
match ($route) {
    'home' => $storefrontController->home(),
    'products' => $storefrontController->products(),
    'product' => $storefrontController->product(),
    'about' => $storefrontController->about(),
    'contact' => $storefrontController->contact(),
    'cart' => $cartController->show(),
    'checkout' => $checkoutController->show(),
    'login' => $authController->loginPage(),
    'register' => $authController->registerPage(),
    'profile' => $authController->profilePage(),
    'orders' => $authController->ordersPage(),
    'admin' => $adminController->index(),
    default => (static function (): void {
        http_response_code(404);
        echo 'Page not found.';
    })(),
};
