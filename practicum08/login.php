<?php

require_once __DIR__ . '/bootstrap.php';
$config = require __DIR__ . '/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $password = $_POST['password'] ?? '';
    if (is_string($password) && password_verify($password, $config['moderator_password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['is_moderator'] = true;
        header('Location: index.php');
        exit;
    }
    http_response_code(401);
    $error = 'Невірний пароль';
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Вхід для модератора</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Вхід для модератора</h1>

    <?php if ($error !== ''): ?>
        <p class="error"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post" action="login.php" class="stacked">
        <?= csrf_field() ?>
        <label for="password">Пароль</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Увійти</button>
        <a href="index.php">Скасувати</a>
    </form>
</body>
</html>