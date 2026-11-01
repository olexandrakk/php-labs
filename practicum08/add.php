<?php

require_once __DIR__ . '/bootstrap.php';
$pdo = require __DIR__ . '/db.php';

require_post();
verify_csrf();

[$errors, $values] = validate_topic($_POST);
if ($errors) {
    abort(400, implode('; ', $errors));
}

$stmt = $pdo->prepare('INSERT INTO topics (title, author, is_pinned) VALUES (:title, :author, 0)');
$stmt->execute([':title' => $values['title'], ':author' => $values['author']]);

header('Location: index.php');
exit;