<?php

$pdo = require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');

if ($q !== '') {
    $stmt = $pdo->prepare('SELECT * FROM topics WHERE author LIKE :q ORDER BY id DESC');
    $stmt->execute([':q' => '%' . $q . '%']);
} else {
    $stmt = $pdo->query('SELECT * FROM topics ORDER BY id DESC');
}

echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);