<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$pdo = require_once __DIR__ . '/db.php';
require_once __DIR__ . '/TopicRepository.php';

$repo = new TopicRepository($pdo);

$authorFilter = trim($_GET['author'] ?? '');
if ($authorFilter !== '') {
    $topics = $repo->listByAuthor($authorFilter);
} elseif (isset($_GET['pinned'])) {
    $topics = $repo->listPinned();
} else {
    $topics = $repo->getAll();
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Форум — теми (PDO)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Форум: теми</h1>

    <form method="get" action="index.php" class="inline-form">
        <input type="text" name="author" placeholder="Автор" value="<?= htmlspecialchars($authorFilter) ?>">
        <button type="submit">Шукати за автором</button>
        <a href="index.php?pinned=1">Лише закріплені</a>
        <a href="index.php">Показати всі</a>
    </form>

    <table>
        <thead>
            <tr><th>ID</th><th>Назва</th><th>Автор</th><th>Створено</th><th>Закріплено</th><th>Дії</th></tr>
        </thead>
        <tbody>
        <?php foreach ($topics as $topic): ?>
            <tr>
                <td><?= $topic['id'] ?></td>
                <td><?= htmlspecialchars($topic['title']) ?></td>
                <td><?= htmlspecialchars($topic['author']) ?></td>
                <td><?= htmlspecialchars($topic['created_at']) ?></td>
                <td><?= $topic['is_pinned'] ? 'Так' : 'Ні' ?></td>
                <td class="actions">
                    <a href="edit.php?id=<?= $topic['id'] ?>">Редагувати</a>
                    <form method="post" action="delete.php" onsubmit="return confirm('Видалити цю тему?');">
                        <input type="hidden" name="id" value="<?= $topic['id'] ?>">
                        <button type="submit" class="danger">Видалити</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($topics)): ?>
            <tr><td colspan="6">Тем не знайдено</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <h2>Нова тема</h2>
    <form method="post" action="add.php">
        <label for="title">Назва</label>
        <input type="text" id="title" name="title" required maxlength="100">

        <label for="author">Автор</label>
        <input type="text" id="author" name="author" required maxlength="100">

        <label class="checkbox"><input type="checkbox" name="is_pinned" value="1"> Закріпити</label>

        <button type="submit">Додати</button>
    </form>
</body>
</html>