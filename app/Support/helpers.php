<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function url(string $route = 'home', array $query = [], string $fragment = ''): string
{
    if ($route === 'admin') {
        $location = APP_URL . '/admin';
        if ($query) {
            $location .= '?' . http_build_query($query);
        }
        if ($fragment !== '') {
            $location .= '#' . rawurlencode(ltrim($fragment, '#'));
        }
        return $location;
    }

    $parameters = $route === 'home' ? $query : ['route' => $route] + $query;
    $location = APP_URL . '/index.php';
    if ($parameters) {
        $location .= '?' . http_build_query($parameters);
    }
    if ($fragment !== '') {
        $location .= '#' . rawurlencode(ltrim($fragment, '#'));
    }
    return $location;
}

function asset(string $path): string
{
    return APP_URL . '/assets/' . ltrim($path, '/');
}

function redirectTo(string $route = 'home', array $query = [], string $fragment = ''): never
{
    header('Location: ' . url($route, $query, $fragment));
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
        redirectTo('login');
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

function render(string $view, array $data = []): void
{
    $path = APP_PATH . '/Views/' . ltrim($view, '/') . '.php';
    if (!is_file($path)) {
        throw new RuntimeException("View not found: {$view}");
    }

    extract($data, EXTR_SKIP);
    require $path;
}
