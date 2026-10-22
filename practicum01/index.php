<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$topics = [
    ['title' => 'Як налаштувати XAMPP для PHP?',      'author' => 'Андрій К.',  'repliesCount' => 34, 'createdAt' => '2026-10-01'],
    ['title' => 'Різниця між echo та print',          'author' => 'Марія С.',   'repliesCount' => 12, 'createdAt' => '2026-10-05'],
    ['title' => 'Асоціативні масиви: приклади',       'author' => 'Олег П.',    'repliesCount' => 27, 'createdAt' => '2026-10-08'],
    ['title' => 'Помилка "Undefined variable"',       'author' => 'Іра Д.',     'repliesCount' => 5,  'createdAt' => '2026-10-12'],
    ['title' => 'Який редактор коду обрати?',         'author' => 'Дмитро Л.',  'repliesCount' => 21, 'createdAt' => '2026-10-15'],
    ['title' => 'Питання щодо практичної роботи №1',  'author' => 'Софія Н.',   'repliesCount' => 20, 'createdAt' => '2026-10-20'],
];

function formatTopic(array $topic): string
{
    return "{$topic['title']} (автор: {$topic['author']}, {$topic['createdAt']})";
}

function getLabel(array $topic): string
{
    if ($topic['repliesCount'] > 20) {
        return 'Гаряча тема';
    }
    return 'Звичайна тема';
}

$totalReplies = 0;
foreach ($topics as $topic) {
    $totalReplies += $topic['repliesCount'];
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Форум — дошка обговорень</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Форум: дошка обговорень</h1>

    <table>
        <thead>
            <tr>
                <th>№</th>
                <th>Тема</th>
                <th>Відповідей</th>
                <th>Мітка</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($topics as $i => $topic): ?>
            <?php $label = getLabel($topic); ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars(formatTopic($topic)) ?></td>
                <td><?= $topic['repliesCount'] ?></td>
                <td>
                    <span class="badge <?= $label === 'Гаряча тема' ? 'hot' : 'normal' ?>">
                        <?= $label ?>
                    </span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="summary">
        Усього тем: <strong><?= count($topics) ?></strong><br>
        Сумарна кількість відповідей: <strong><?= $totalReplies ?></strong>
    </div>
</body>
</html>