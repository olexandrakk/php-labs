<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$pdo = require_once __DIR__ . '/db.php';
require_once __DIR__ . '/TopicRepository.php';

$repo = new TopicRepository($pdo);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isPinned = isset($_POST['is_pinned']);

    if ($title === '' || $author === '' || mb_strlen($title) > 100 || mb_strlen($author) > 100) {
        $error = 'Назва й автор обов\'язкові (до 100 символів)';
        $topic = ['id' => $id, 'title' => $title, 'author' => $author, 'is_pinned' => $isPinned ? 1 : 0];
    } else {
        $repo->updateTopic($id, $title, $author, $isPinned);
        header('Location: index.php');
        exit;
    }
} else {
    $topic = $repo->find((int) ($_GET['id'] ?? 0));
    if ($topic === null) {
        http_response_code(404);
        echo 'Тему не знайдено. <a href="index.php">Назад</a>';
        exit;
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

    <?php if ($error !== ''): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="edit.php">
        <input type="hidden" name="id" value="<?= (int) $topic['id'] ?>">

        <label for="title">Назва</label>
        <input type="text" id="title" name="title" required maxlength="100"
               value="<?= htmlspecialchars($topic['title']) ?>">

        <label for="author">Автор</label>
        <input type="text" id="author" name="author" required maxlength="100"
               value="<?= htmlspecialchars($topic['author']) ?>">

        <label class="checkbox">
            <input type="checkbox" name="is_pinned" value="1" <?= $topic['is_pinned'] ? 'checked' : '' ?>> Закріпити
        </label>

        <button type="submit">Зберегти</button>
        <a href="index.php">Скасувати</a>
    </form>
</body>
</html>