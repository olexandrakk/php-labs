<?php

$dsn = 'mysql:host=localhost;dbname=practicum4;charset=utf8mb4';

try {
    $pdo = new PDO($dsn, 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(
        ['success' => false, 'error' => 'Не вдалося підключитися до бази даних'],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

return $pdo;