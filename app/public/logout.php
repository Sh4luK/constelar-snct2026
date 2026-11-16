<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

exigir_post_com_csrf();
encerrar_sessao();
redirecionar('/');
