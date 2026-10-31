<?php

header('Content-Type: application/json; charset=utf-8');

$pdo = require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['resource'] ?? null;

function respond(int $code, array $payload): void
{
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function fail(int $code, string $message): void
{
    respond($code, ['success' => false, 'error' => $message]);
}

function normalize(array $topic): array
{
    $topic['id'] = (int) $topic['id'];
    $topic['is_pinned'] = (int) $topic['is_pinned'];
    return $topic;
}

function parseId($value): int
{
    if ($value === null || !ctype_digit((string) $value) || (int) $value < 1) {
        fail(400, 'Параметр id має бути додатним цілим числом');
    }
    return (int) $value;
}

function findTopic(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM topics WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    return $row === false ? null : normalize($row);
}

function readInput(): array
{
    $json = json_decode(file_get_contents('php://input'), true);
    return is_array($json) ? $json : $_POST;
}

function listTopics(PDO $pdo): void
{
    $rows = $pdo->query('SELECT * FROM topics ORDER BY id DESC')->fetchAll();
    respond(200, ['success' => true, 'data' => array_map('normalize', $rows)]);
}

function getTopic(PDO $pdo, $rawId): void
{
    $id = parseId($rawId);
    $topic = findTopic($pdo, $id);
    if ($topic === null) {
        fail(404, "Тему з id $id не знайдено");
    }
    respond(200, ['success' => true, 'data' => $topic]);
}

function createTopic(PDO $pdo, array $input): void
{
    $title = trim((string) ($input['title'] ?? ''));
    $author = trim((string) ($input['author'] ?? ''));
    $isPinned = !empty($input['is_pinned']) ? 1 : 0;

    if ($title === '') {
        fail(400, 'Поле title обов\'язкове');
    }
    if (mb_strlen($title) > 100) {
        fail(400, 'Поле title не може бути довшим за 100 символів');
    }
    if ($author === '') {
        fail(400, 'Поле author обов\'язкове');
    }
    if (mb_strlen($author) > 100) {
        fail(400, 'Поле author не може бути довшим за 100 символів');
    }

    $stmt = $pdo->prepare(
        'INSERT INTO topics (title, author, is_pinned) VALUES (:title, :author, :is_pinned)'
    );
    $stmt->execute([':title' => $title, ':author' => $author, ':is_pinned' => $isPinned]);

    respond(201, ['success' => true, 'data' => findTopic($pdo, (int) $pdo->lastInsertId())]);
}

function pinTopic(PDO $pdo, $rawId): void
{
    $id = parseId($rawId);
    if (findTopic($pdo, $id) === null) {
        fail(404, "Тему з id $id не знайдено");
    }

    $stmt = $pdo->prepare('UPDATE topics SET is_pinned = 1 WHERE id = :id');
    $stmt->execute([':id' => $id]);

    respond(200, ['success' => true, 'data' => findTopic($pdo, $id)]);
}

try {
    if ($resource === 'topics') {
        if ($method === 'GET') {
            if (isset($_GET['id'])) {
                getTopic($pdo, $_GET['id']);
            } else {
                listTopics($pdo);
            }
        } elseif ($method === 'POST') {
            $input = readInput();
            $action = $_GET['action'] ?? ($input['action'] ?? null);

            if ($action === null) {
                createTopic($pdo, $input);
            } elseif ($action === 'pin') {
                pinTopic($pdo, $_GET['id'] ?? ($input['id'] ?? null));
            } else {
                fail(400, 'Невідома дія action: ' . (string) $action);
            }
        } else {
            header('Allow: GET, POST');
            fail(405, "Метод $method не підтримується для ресурсу topics");
        }
    } else {
        fail(404, 'Невідомий ресурс');
    }
} catch (PDOException $e) {
    fail(500, 'Помилка бази даних');
}