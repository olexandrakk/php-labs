<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$pdo = require_once __DIR__ . '/db.php';
require_once __DIR__ . '/TopicRepository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$title = trim($_POST['title'] ?? '');
$author = trim($_POST['author'] ?? '');
$isPinned = isset($_POST['is_pinned']);

if ($title === '' || $author === '' || mb_strlen($title) > 100 || mb_strlen($author) > 100) {
    http_response_code(400);
    echo 'Помилка: назва й автор обов\'язкові (до 100 символів). <a href="index.php">Назад</a>';
    exit;
}

$repo = new TopicRepository($pdo);
$repo->addTopic($title, $author, $isPinned);

header('Location: index.php');
exit;