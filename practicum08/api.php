<?php

const API_MODE = true;

require_once __DIR__ . '/bootstrap.php';
$pdo = require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

function respond(int $code, $data): void
{
    http_response_code($code);
    echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

function read_input(): array
{
    $json = json_decode(file_get_contents('php://input'), true);
    return is_array($json) ? $json : $_POST;
}

$method = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['resource'] ?? null;

if ($resource === 'csrf') {
    if ($method !== 'GET') {
        header('Allow: GET');
        abort(405, "Метод $method не підтримується для ресурсу csrf");
    }
    respond(200, ['csrf_token' => csrf_token()]);
}

if ($resource !== 'topics') {
    abort(404, 'Невідомий ресурс');
}

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $topic = find_topic($pdo, parse_id($_GET['id']));
        if ($topic === null) {
            abort(404, 'Тему не знайдено');
        }
        respond(200, $topic);
    }

    $author = str_field($_GET, 'author');
    if ($author !== '') {
        $stmt = $pdo->prepare('SELECT * FROM topics WHERE author = :author ORDER BY id DESC');
        $stmt->execute([':author' => $author]);
    } else {
        $stmt = $pdo->prepare('SELECT * FROM topics ORDER BY id DESC');
        $stmt->execute();
    }
    respond(200, $stmt->fetchAll());
}

if ($method === 'POST') {
    $input = read_input();
    $bodyToken = isset($input['csrf_token']) && is_string($input['csrf_token']) ? $input['csrf_token'] : null;
    verify_csrf($bodyToken);

    $action = $_GET['action'] ?? ($input['action'] ?? null);

    if ($action === null) {
        [$errors, $values] = validate_topic($input);
        if ($errors) {
            abort(400, implode('; ', $errors));
        }
        $stmt = $pdo->prepare('INSERT INTO topics (title, author, is_pinned) VALUES (:title, :author, 0)');
        $stmt->execute([':title' => $values['title'], ':author' => $values['author']]);
        respond(201, find_topic($pdo, (int) $pdo->lastInsertId()));
    }

    if ($action === 'pin') {
        require_moderator();
        $id = parse_id($_GET['id'] ?? ($input['id'] ?? null));
        if (find_topic($pdo, $id) === null) {
            abort(404, 'Тему не знайдено');
        }
        $stmt = $pdo->prepare('UPDATE topics SET is_pinned = 1 WHERE id = :id');
        $stmt->execute([':id' => $id]);
        respond(200, find_topic($pdo, $id));
    }

    abort(400, 'Невідома дія action');
}

header('Allow: GET, POST');
abort(405, "Метод $method не підтримується для ресурсу topics");