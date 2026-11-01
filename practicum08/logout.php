<?php

require_once __DIR__ . '/bootstrap.php';

require_post();
verify_csrf();

$_SESSION = [];
setcookie(session_name(), '', time() - 3600, '/');
session_destroy();

header('Location: index.php');
exit;