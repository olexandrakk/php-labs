<?php

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'self'; frame-ancestors 'none'");

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function abort(int $code, string $message): never
{
    http_response_code($code);
    if (defined('API_MODE')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    } else {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html lang="uk"><head><meta charset="UTF-8"><title>Помилка</title>'
            . '<link rel="stylesheet" href="style.css"></head><body><h1>Помилка ' . $code . '</h1>'
            . '<p>' . e($message) . '</p><p><a href="index.php">На головну</a></p></body></html>';
    }
    exit;
}

set_exception_handler(function (Throwable $error): void {
    error_log(sprintf('%s: %s in %s:%d', get_class($error), $error->getMessage(), $error->getFile(), $error->getLine()));
    abort(500, 'Внутрішня помилка сервера. Спробуйте пізніше.');
});

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token = null): void
{
    $sent = $token ?? str_field($_POST, 'csrf_token');
    if ($sent === '') {
        $header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $sent = is_string($header) ? $header : '';
    }
    $expected = $_SESSION['csrf_token'] ?? '';
    if ($expected === '' || !hash_equals($expected, $sent)) {
        abort(403, 'Недійсний CSRF-токен');
    }
}

function is_moderator(): bool
{
    return !empty($_SESSION['is_moderator']);
}

function require_moderator(): void
{
    if (!is_moderator()) {
        abort(403, 'Потрібні права модератора');
    }
}

function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: POST');
        abort(405, 'Дозволено лише метод POST');
    }
}

function str_field(array $source, string $key): string
{
    $value = $source[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

function parse_id($value): int
{
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        abort(400, 'Некоректний id');
    }
    return $id;
}

function validate_topic(array $input): array
{
    $title = str_field($input, 'title');
    $author = str_field($input, 'author');
    $errors = [];

    if ($title === '') {
        $errors[] = 'Поле title обов\'язкове';
    } elseif (mb_strlen($title) > 100) {
        $errors[] = 'Поле title не може бути довшим за 100 символів';
    }

    if ($author === '') {
        $errors[] = 'Поле author обов\'язкове';
    } elseif (mb_strlen($author) > 100) {
        $errors[] = 'Поле author не може бути довшим за 100 символів';
    }

    $values = [
        'title' => $title,
        'author' => $author,
        'is_pinned' => empty($input['is_pinned']) ? 0 : 1,
    ];

    return [$errors, $values];
}

function find_topic(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM topics WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}