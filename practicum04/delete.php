<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$pdo = require_once __DIR__ . '/db.php';
require_once __DIR__ . '/TopicRepository.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $repo = new TopicRepository($pdo);
    $repo->deleteTopic((int) ($_POST['id'] ?? 0));
}

header('Location: index.php');
exit;