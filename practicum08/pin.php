<?php

require_once __DIR__ . '/bootstrap.php';
$pdo = require __DIR__ . '/db.php';

require_post();
verify_csrf();
require_moderator();

$id = parse_id($_POST['id'] ?? null);
if (find_topic($pdo, $id) === null) {
    abort(404, 'Тему не знайдено');
}

$stmt = $pdo->prepare('UPDATE topics SET is_pinned = 1 WHERE id = :id');
$stmt->execute([':id' => $id]);

header('Location: index.php');
exit;