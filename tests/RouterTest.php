<?php
declare(strict_types=1);

putenv('APP_URL=/gamegear/public');
require dirname(__DIR__) . '/config/app.php';
require APP_PATH . '/Support/helpers.php';

$cases = [
    url('home') => '/gamegear/public/index.php',
    url('products', ['category' => 2]) => '/gamegear/public/index.php?route=products&category=2',
    url('product', ['id' => 7], 'reviews') => '/gamegear/public/index.php?route=product&id=7#reviews',
    url('admin') => '/gamegear/public/admin',
    url('admin', ['tab' => 'products'], 'product-form') => '/gamegear/public/admin?tab=products#product-form',
    asset('css/style.css') => '/gamegear/public/assets/css/style.css',
];

foreach ($cases as $actual => $expected) {
    if ($actual !== $expected) {
        fwrite(STDERR, "Expected {$expected}, got {$actual}" . PHP_EOL);
        exit(1);
    }
}

echo "Router test passed." . PHP_EOL;
