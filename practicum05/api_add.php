<?php

$pdo = require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

function respond(int $code, array $payload): void
{
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['error' => 'Дозволений лише метод POST']);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$title = trim((string) ($input['title'] ?? ''));
$author = trim((string) ($input['author'] ?? ''));
$isPinned = !empty($input['is_pinned']) ? 1 : 0;

if ($title === '' || $author === '') {
    respond(400, ['error' => 'Назва й автор обов\'язкові']);
}
if (mb_strlen($title) > 100 || mb_strlen($author) > 100) {
    respond(400, ['error' => 'Назва й автор — не довші за 100 символів']);
}

$stmt = $pdo->prepare(
    'INSERT INTO topics (title, author, is_pinned) VALUES (:title, :author, :is_pinned)'
);
$stmt->execute([
    ':title' => $title,
    ':author' => $author,
    ':is_pinned' => $isPinned,
]);

$select = $pdo->prepare('SELECT * FROM topics WHERE id = :id');
$select->execute([':id' => $pdo->lastInsertId()]);

respond(201, $select->fetch());