<?php
declare(strict_types=1);
require __DIR__ . '/../src/banco.php';

// Usado pelo Docker e pelo iniciar.sh: 200 quando o site consegue falar com o banco.
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
try {
    db()->query('SELECT 1 FROM migracoes LIMIT 1');
    echo 'ok';
} catch (Throwable $e) {
    http_response_code(503);
    echo 'indisponivel';
}
