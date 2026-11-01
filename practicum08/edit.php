<?php

require_once __DIR__ . '/bootstrap.php';
$pdo = require __DIR__ . '/db.php';

require_moderator();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = parse_id($_POST['id'] ?? null);
    if (find_topic($pdo, $id) === null) {
        abort(404, 'Тему не знайдено');
    }

    [$errors, $values] = validate_topic($_POST);
    if (!$errors) {
        $stmt = $pdo->prepare(
            'UPDATE topics SET title = :title, author = :author, is_pinned = :is_pinned WHERE id = :id'
        );
        $stmt->execute([
            ':title' => $values['title'],
            ':author' => $values['author'],
            ':is_pinned' => $values['is_pinned'],
            ':id' => $id,
        ]);
        header('Location: index.php');
        exit;
    }
    $topic = ['id' => $id] + $values;
} else {
    $topic = find_topic($pdo, parse_id($_GET['id'] ?? null));
    if ($topic === null) {
        abort(404, 'Тему не знайдено');
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Редагувати тему</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Редагувати тему</h1>

    <?php foreach ($errors as $error): ?>
        <p class="error"><?= e($error) ?></p>
    <?php endforeach; ?>

    <form method="post" action="edit.php" class="stacked">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $topic['id'] ?>">

        <label for="title">Назва</label>
        <input type="text" id="title" name="title" required maxlength="100" value="<?= e($topic['title']) ?>">

        <label for="author">Автор</label>
        <input type="text" id="author" name="author" required maxlength="100" value="<?= e($topic['author']) ?>">

        <label class="checkbox">
            <input type="checkbox" name="is_pinned" value="1" <?= $topic['is_pinned'] ? 'checked' : '' ?>> Закріпити
        </label>

        <button type="submit">Зберегти</button>
        <a href="index.php">Скасувати</a>
    </form>
</body>
</html>