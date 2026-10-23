<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/classes/Topic.php';
require_once __DIR__ . '/classes/PinnedTopic.php';
require_once __DIR__ . '/classes/Forum.php';
require_once __DIR__ . '/lib/functions.php';

function ago(string $modifier): string
{
    return date('Y-m-d H:i:s', strtotime($modifier));
}

$forum = new Forum();

$forum->addTopic(new Topic('Як налаштувати XAMPP для PHP?', 'Андрій К.', ago('-3 days')));
$forum->addTopic(new Topic('Різниця між echo та print', 'Марія С.', ago('-5 hours')));
$forum->addTopic(new Topic('Асоціативні масиви: приклади', 'Андрій К.', ago('-30 minutes')));

$forum->addTopic(new PinnedTopic('Правила форуму', 'Адміністратор', ago('-10 days'), ago('+30 days')));
$forum->addTopic(new PinnedTopic('Розклад консультацій', 'Викладач', ago('-2 days'), ago('+5 days')));
$forum->addTopic(new PinnedTopic('Конкурс (завершено)', 'Адміністратор', ago('-20 days'), ago('-1 days')));
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Форум — ООП</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Форум: теми (ООП)</h1>

    <h2>Усі теми</h2>
    <table>
        <thead>
            <tr><th>№</th><th>Тип</th><th>Інформація</th><th>Створено</th></tr>
        </thead>
        <tbody>
        <?php foreach ($forum->getAll() as $i => $topic): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= get_class($topic) ?></td>
                <td><?= sanitizeText($topic->getInfo()) ?></td>
                <td><?= timeAgo($topic->getCreatedAt()) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Закріплені зараз (listPinned)</h2>
    <ul>
        <?php foreach ($forum->listPinned() as $topic): ?>
            <li><?= sanitizeText($topic->getInfo()) ?></li>
        <?php endforeach; ?>
    </ul>

    <h2>Теми автора «Андрій К.» (listByAuthor)</h2>
    <ul>
        <?php foreach ($forum->listByAuthor('Андрій К.') as $topic): ?>
            <li><?= sanitizeText($topic->getInfo()) ?> — <?= timeAgo($topic->getCreatedAt()) ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>