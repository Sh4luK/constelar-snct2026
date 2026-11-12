<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

exigir_post_com_csrf();
$_SESSION = [];
$p = session_get_cookie_params();
setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => $p['path'], 'secure' => $p['secure'], 'httponly' => true, 'samesite' => $p['samesite']]);
session_destroy();
redirecionar('/');
