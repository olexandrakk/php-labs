<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$errors = [];
$success = false;
$title = '';
$author = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($title === '') {
        $errors['title'] = 'Назва теми обов\'язкова';
    } elseif (mb_strlen($title) > 100) {
        $errors['title'] = 'Назва не може бути довшою за 100 символів';
    } elseif (!preg_match('/^[\p{L}\p{N}\s.,!?:\-]+$/u', $title)) {
        $errors['title'] = 'Назва містить заборонені символи (дозволені літери, цифри, пробіл та . , ! ? : -)';
    }

    if ($author === '') {
        $errors['author'] = 'Автор обов\'язковий';
    }

    if (mb_strlen($message) < 10) {
        $errors['message'] = 'Повідомлення має містити щонайменше 10 символів';
    }

    $success = empty($errors);
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Форум — нова тема</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Форум: нова тема</h1>

<?php if ($success): ?>
    <div class="success">
        <p>Тему прийнято!</p>
        <p><strong>Назва:</strong> <?= htmlspecialchars($title) ?></p>
        <p><strong>Автор:</strong> <?= htmlspecialchars($author) ?></p>
        <p><strong>Повідомлення:</strong><br><?= nl2br(htmlspecialchars($message)) ?></p>
        <p><a href="form.php">Створити ще одну тему</a></p>
    </div>
<?php else: ?>
    <form id="topicForm" method="post" action="form.php">
        <label for="title">Назва теми</label>
        <input type="text" id="title" name="title" required maxlength="100"
               value="<?= htmlspecialchars($title) ?>">
        <div class="error" id="titleError"><?= isset($errors['title']) ? htmlspecialchars($errors['title']) : '' ?></div>

        <label for="author">Автор</label>
        <input type="text" id="author" name="author" required
               value="<?= htmlspecialchars($author) ?>">
        <div class="error" id="authorError"><?= isset($errors['author']) ? htmlspecialchars($errors['author']) : '' ?></div>

        <label for="message">Повідомлення</label>
        <textarea id="message" name="message" rows="6" required minlength="10"><?= htmlspecialchars($message) ?></textarea>
        <div class="error" id="messageError"><?= isset($errors['message']) ? htmlspecialchars($errors['message']) : '' ?></div>

        <button type="submit">Створити тему</button>
    </form>
<?php endif; ?>

    <script>
        const DRAFT_KEY = 'forum_draft_message';
        const success = <?= $success ? 'true' : 'false' ?>;

        if (success) {
            localStorage.removeItem(DRAFT_KEY);
        } else {
            const form = document.getElementById('topicForm');
            const titleInput = document.getElementById('title');
            const authorInput = document.getElementById('author');
            const messageInput = document.getElementById('message');

            if (messageInput.value === '') {
                messageInput.value = localStorage.getItem(DRAFT_KEY) || '';
            }

            messageInput.addEventListener('input', () => {
                localStorage.setItem(DRAFT_KEY, messageInput.value);
            });

            form.addEventListener('submit', (event) => {
                let hasErrors = false;
                const titleError = document.getElementById('titleError');
                const authorError = document.getElementById('authorError');
                const messageError = document.getElementById('messageError');
                titleError.textContent = '';
                authorError.textContent = '';
                messageError.textContent = '';

                const title = titleInput.value.trim();
                if (title === '') {
                    titleError.textContent = 'Назва теми обов\'язкова';
                    hasErrors = true;
                } else if (!/^[\p{L}\p{N}\s.,!?:\-]+$/u.test(title)) {
                    titleError.textContent = 'Назва містить заборонені символи (дозволені літери, цифри, пробіл та . , ! ? : -)';
                    hasErrors = true;
                }

                if (authorInput.value.trim() === '') {
                    authorError.textContent = 'Автор обов\'язковий';
                    hasErrors = true;
                }

                if (messageInput.value.trim().length < 10) {
                    messageError.textContent = 'Повідомлення має містити щонайменше 10 символів';
                    hasErrors = true;
                }

                if (hasErrors) {
                    event.preventDefault();
                }
            });
        }
    </script>
</body>
</html>