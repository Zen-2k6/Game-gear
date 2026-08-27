<?php
declare(strict_types=1);

// var_dump('hello');

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function appUrl(string $path = ''): string
{
    return rtrim(APP_URL, '/') . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

function redirect(string $page): never
{
    $location = str_starts_with($page, '/') || preg_match('#^https?://#', $page) ? $page : appUrl($page);
    header('Location: ' . $location);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verifyCsrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(403);
        if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'message' => 'Your session expired. Refresh the page and try again.']);
            exit;
        }
        exit('Invalid form token. Please return and try again.');
    }
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function requireLogin(): void
{
    if (!currentUser()) {
        flash('error', 'Please log in to continue.');
        redirect('user/login.php');
    }
}

function requireAdmin(): void
{
    if ((currentUser()['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Administrator access required.');
    }
}

function money(float|string $amount): string
{
    return number_format((float) $amount) . ' MMK';
}

function cartCount(): int
{
    return array_sum($_SESSION['cart'] ?? []);
}

function cartProducts(PDO $pdo): array
{
    $cart = $_SESSION['cart'] ?? [];
    if (!$cart) return [];
    $ids = array_keys($cart);
    $marks = implode(',', array_fill(0, count($ids), '?'));
    $statement = $pdo->prepare("SELECT productID AS id,productName AS name,description,specifications,price,stockQuantity AS stock,imageURL AS image_url FROM Product WHERE productID IN ($marks) AND status='Active'");
    $statement->execute($ids);
    $items = [];
    foreach ($statement->fetchAll() as $product) {
        $product['quantity'] = min((int) $cart[$product['id']], (int) $product['stock']);
        $product['subtotal'] = $product['quantity'] * (float) $product['price'];
        $items[] = $product;
    }
    return $items;
}
