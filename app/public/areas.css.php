<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

// Cor de cada constelação como classe (.area-N { --cor }): a CSP do site não permite style inline.
$css = '';
foreach (areas() as $a) {
    if (preg_match('/^#[0-9a-fA-F]{6}$/', $a['cor'])) {
        $css .= '.area-' . $a['id'] . '{--cor:' . strtolower($a['cor']) . "}\n";
    }
}

$etag = '"' . md5($css) . '"';
header('Content-Type: text/css; charset=utf-8');
header('Cache-Control: no-cache');
header('ETag: ' . $etag);
if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
    http_response_code(304);
    exit;
}
echo $css;
