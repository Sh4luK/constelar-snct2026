<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

// Dados do jogo: apenas cientistas publicadas (aprovadas e com autorização registrada).
$areas = array_map(fn ($a) => [
    'id' => $a['id'], 'nome' => $a['nome'], 'constelacao' => $a['constelacao'],
    'descricao' => $a['descricao'], 'cor' => $a['cor'], 'icone' => $a['icone'],
], areas());

$cientistas = array_map(fn ($c) => [
    'id' => $c['id'], 'numero' => $c['numero'], 'nome' => $c['nome'], 'area_id' => $c['area_id'],
    'instituicao' => $c['instituicao'], 'biografia' => $c['biografia'], 'curiosidade' => $c['curiosidade'],
    'destaque' => $c['destaque'], 'variante' => $c['variante'],
], cientistas_publicadas());

$json = json_encode(['areas' => $areas, 'cientistas' => $cientistas], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
$etag = '"' . md5($json) . '"';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache');
header('ETag: ' . $etag);
if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
    http_response_code(304);
    exit;
}
echo $json;
