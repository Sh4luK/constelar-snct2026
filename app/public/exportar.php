<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

// Exporta o resultado de uma busca do acervo (mesmos filtros da página /cientistas.php).
$f = filtros_da_url($_GET);
$formato = ($_GET['formato'] ?? 'csv') === 'json' ? 'json' : 'csv';
$r = buscar_cientistas($f, null);
$arquivo = 'constelar-acervo-' . date('Y-m-d');

$linhas = array_map(fn ($c) => [
    'numero'      => $c['numero'],
    'nome'        => $c['nome'],
    'area'        => $c['area'],
    'constelacao' => $c['constelacao'],
    'instituicao' => (string) $c['instituicao'],
    'temas'       => implode('; ', array_column($c['temas'], 'nome')),
    'biografia'   => $c['biografia'],
    'curiosidade' => (string) $c['curiosidade'],
    'fontes'      => $c['fonte'],
    'pagina'      => 'cientista.php?id=' . $c['id'],
], $r['itens']);

header('Cache-Control: no-cache');
if ($formato === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $arquivo . '.json"');
    echo json_encode(['gerado_em' => date('c'), 'total' => $r['total'], 'filtros' => array_diff_key($f, ['pagina' => 1]), 'cientistas' => $linhas],
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    exit;
}

// CSV para Excel/LibreOffice em português: BOM UTF-8 e ponto e vírgula.
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $arquivo . '.csv"');
$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");
fputcsv($out, ['Nº', 'Nome', 'Área', 'Constelação', 'Instituição', 'Temas', 'Biografia', 'Curiosidade', 'Fontes', 'Página'], ';', '"', '');
foreach ($linhas as $l) {
    // Evita que o Excel interprete um campo como fórmula.
    $l = array_map(fn ($v) => is_string($v) && preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v, $l);
    fputcsv($out, array_values($l), ';', '"', '');
}
fclose($out);
