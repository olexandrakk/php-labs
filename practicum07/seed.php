<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Лише з командного рядка');
}

$pdo = require __DIR__ . '/db.php';

$arg = $argv[1] ?? '1000';

if ($arg === 'clean') {
    $stmt = $pdo->prepare("DELETE FROM topics WHERE author LIKE 'Seed Author %'");
    $stmt->execute();
    echo 'Видалено тестових тем: ' . $stmt->rowCount() . PHP_EOL;
    exit;
}

$n = max(1, (int) $arg);

$pdo->beginTransaction();
$stmt = $pdo->prepare(
    'INSERT INTO topics (title, author, is_pinned) VALUES (:title, :author, :is_pinned)'
);
for ($i = 1; $i <= $n; $i++) {
    $stmt->execute([
        ':title' => "Seed topic $i",
        ':author' => 'Seed Author ' . mt_rand(1, 30),
        ':is_pinned' => $i % 50 === 0 ? 1 : 0,
    ]);
}
$pdo->commit();

echo "Додано тем: $n" . PHP_EOL;