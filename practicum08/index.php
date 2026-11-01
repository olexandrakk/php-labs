<?php

require_once __DIR__ . '/bootstrap.php';
$pdo = require __DIR__ . '/db.php';

$author = str_field($_GET, 'author');

if ($author !== '') {
    $stmt = $pdo->prepare('SELECT * FROM topics WHERE author = :author ORDER BY id DESC');
    $stmt->execute([':author' => $author]);
} else {
    $stmt = $pdo->prepare('SELECT * FROM topics ORDER BY id DESC');
    $stmt->execute();
}
$topics = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Форум — безпечна версія</title>
    <link rel="stylesheet" href="style.css">
    <script src="app.js" defer></script>
</head>
<body>
    <h1>Форум: теми</h1>

    <div class="auth">
        <?php if (is_moderator()): ?>
            <span>Ви увійшли як модератор</span>
            <form method="post" action="logout.php" class="inline">
                <?= csrf_field() ?>
                <button type="submit" class="secondary">Вийти</button>
            </form>
        <?php else: ?>
            <a href="login.php">Вхід для модератора</a>
        <?php endif; ?>
    </div>

    <form method="get" action="index.php" class="inline">
        <input type="text" name="author" placeholder="Автор" value="<?= e($author) ?>">
        <button type="submit">Шукати за автором</button>
        <a href="index.php">Показати всі</a>
    </form>

    <table>
        <thead>
            <tr><th>ID</th><th>Назва</th><th>Автор</th><th>Створено</th><th>Закріплено</th><th>Дії</th></tr>
        </thead>
        <tbody>
        <?php foreach ($topics as $topic): ?>
            <tr>
                <td><?= (int) $topic['id'] ?></td>
                <td><?= e($topic['title']) ?></td>
                <td><?= e($topic['author']) ?></td>
                <td><?= e($topic['created_at']) ?></td>
                <td><?= $topic['is_pinned'] ? 'Так' : 'Ні' ?></td>
                <td class="actions">
                    <?php if (is_moderator()): ?>
                        <a href="edit.php?id=<?= (int) $topic['id'] ?>">Редагувати</a>
                        <?php if (!$topic['is_pinned']): ?>
                            <form method="post" action="pin.php" class="inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $topic['id'] ?>">
                                <button type="submit">Закріпити</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="delete.php" class="inline" data-confirm="Видалити цю тему?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $topic['id'] ?>">
                            <button type="submit" class="secondary">Видалити</button>
                        </form>
                    <?php else: ?>
                        <span>—</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($topics)): ?>
            <tr><td colspan="6">Тем не знайдено</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <h2>Нова тема</h2>
    <form method="post" action="add.php" class="stacked">
        <?= csrf_field() ?>
        <label for="title">Назва</label>
        <input type="text" id="title" name="title" required maxlength="100">

        <label for="author">Автор</label>
        <input type="text" id="author" name="author" required maxlength="100">

        <button type="submit">Додати</button>
    </form>
</body>
</html>