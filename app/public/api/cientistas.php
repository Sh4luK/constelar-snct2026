<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=60');

$areas = db()->query('SELECT id, nome, constelacao, descricao, cor FROM areas ORDER BY id')->fetchAll();
$cientistas = db()->query("SELECT id, nome, area_id, instituicao, biografia, curiosidade
                           FROM cientistas
                           WHERE status = 'aprovado' AND autorizacao_uso = 1
                           ORDER BY area_id, id")->fetchAll();

foreach ($areas as &$a) {
    $a['id'] = (int) $a['id'];
}
foreach ($cientistas as &$c) {
    $c['id'] = (int) $c['id'];
    $c['area_id'] = (int) $c['area_id'];
}
unset($a, $c);

echo json_encode(['areas' => $areas, 'cientistas' => $cientistas], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
