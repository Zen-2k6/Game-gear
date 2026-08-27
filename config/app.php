<?php
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');

function loadEnvFile(string $path): void
{
    if (!is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (str_starts_with($line, 'export ')) {
            $line = trim(substr($line, 7));
        }

        if (!preg_match('/^([A-Z_][A-Z0-9_]*)\s*=\s*(.*)$/i', $line, $matches)) {
            continue;
        }

        $name = $matches[1];
        if (getenv($name) !== false) {
            continue;
        }

        $value = trim($matches[2]);
        if (strlen($value) >= 2) {
            $quote = $value[0];
            if (($quote === '"' || $quote === "'") && str_ends_with($value, $quote)) {
                $value = substr($value, 1, -1);
                if ($quote === '"') {
                    $value = stripcslashes($value);
                }
            }
        }

        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
    }
}

loadEnvFile(ROOT_PATH . '/.env');

function configEnv(string $name, ?string $default = null): string
{
    $value = getenv($name);
    if ($value !== false) {
        return $value;
    }

    if ($default !== null) {
        return $default;
    }

    throw new RuntimeException("Missing required environment variable: {$name}");
}

function detectAppUrl(): string
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    if ($scriptName === '') {
        return '';
    }

    $directory = dirname($scriptName);
    return $directory === '/' || $directory === '.' ? '' : rtrim($directory, '/');
}

define('APP_URL', rtrim(configEnv('APP_URL', detectAppUrl()), '/'));

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = APP_PATH . '/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});
