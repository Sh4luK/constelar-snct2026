<?php
declare(strict_types=1);

/**
 * Baixa da Wikimedia Commons as fotos do acervo inicial que ainda faltam (db/fotos-acervo.json),
 * confere se a licença é livre, aplica o mesmo tratamento das fotos enviadas e grava o crédito.
 * Roda sozinho em segundo plano a cada subida do container; sem internet, só tenta de novo na próxima.
 * Uso manual: docker compose exec app php bin/importar-fotos.php
 */

if (PHP_SAPI !== 'cli') {
    exit(1);
}

require __DIR__ . '/../src/banco.php';
require __DIR__ . '/../src/fotos.php';

const UA = 'ConstelarSNCT/1.0 (projeto escolar; https://github.com/Sh4luK/constelar-snct2026)';
const LICENCAS_LIVRES = '/^(cc0|public domain|pd|cc by(-sa)? [0-9.]+( [a-z]+)?|attribution)$/i';

function baixar(string $url): ?string
{
    $ctx = stream_context_create(['http' => ['header' => 'User-Agent: ' . UA, 'timeout' => 20, 'ignore_errors' => false]]);
    $dados = @file_get_contents($url, false, $ctx);
    return $dados === false ? null : $dados;
}

function log_foto(string $m): void
{
    fwrite(STDOUT, "[constelar] fotos: {$m}\n");
}

if (!function_exists('imagecreatefromstring')) {
    log_foto('extensão GD ausente; importação adiada.');
    exit(0);
}

$lista = json_decode((string) file_get_contents(__DIR__ . '/../db/fotos-acervo.json'), true)['fotos'] ?? [];
try {
    $pdo = db();
} catch (PDOException) {
    exit(0);
}

$faltam = [];
$st = $pdo->prepare('SELECT id FROM cientistas WHERE nome = ? AND foto IS NULL');
foreach ($lista as $nome => $cfg) {
    $st->execute([$nome]);
    if ($id = $st->fetchColumn()) {
        $faltam[(int) $id] = [$nome, $cfg];
    }
}
if (!$faltam) {
    exit(0);
}
log_foto(count($faltam) . ' foto(s) do acervo a buscar na Wikimedia Commons…');

$ok = 0;
foreach ($faltam as $id => [$nome, $cfg]) {
    $arquivo = str_replace(' ', '_', (string) $cfg['arquivo']);

    // 1. Licença e autoria.
    $api = 'https://commons.wikimedia.org/w/api.php?' . http_build_query([
        'action' => 'query', 'titles' => 'File:' . $arquivo, 'prop' => 'imageinfo',
        'iiprop' => 'extmetadata', 'format' => 'json', 'formatversion' => 2,
    ]);
    $json = baixar($api);
    if ($json === null) {
        log_foto('sem acesso à Wikimedia agora; tento de novo na próxima subida.');
        break;
    }
    $meta = json_decode($json, true)['query']['pages'][0]['imageinfo'][0]['extmetadata'] ?? null;
    $licenca = trim(strip_tags((string) ($meta['LicenseShortName']['value'] ?? '')));
    if (!$meta || !preg_match(LICENCAS_LIVRES, $licenca)) {
        log_foto("{$nome}: licença não reconhecida como livre ({$licenca}); ignorada.");
        continue;
    }
    $autor = mb_substr(trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) ($meta['Artist']['value'] ?? 'Autor desconhecido')))) ?? ''), 0, 120);

    // 2. Miniatura de 500 px (tamanho padrão da Wikimedia; baixar originais é desencorajado).
    $h = md5($arquivo);
    $q = rawurlencode($arquivo);
    $tif = (bool) preg_match('/\.tiff?$/i', $arquivo);
    $url = "https://upload.wikimedia.org/wikipedia/commons/thumb/{$h[0]}/" . substr($h, 0, 2) . "/{$q}/"
        . ($tif ? 'lossy-page1-' : '') . '500px-' . $q . ($tif ? '.jpg' : '');
    $dados = baixar($url);
    $img = $dados === null ? false : @imagecreatefromstring($dados);
    if (!$img) {
        log_foto("{$nome}: não consegui baixar a imagem agora.");
        sleep(3);
        continue;
    }

    // 3. Mesmo tratamento das fotos enviadas pelo site.
    $foto = gravar_foto($img, $cfg['foco'] ?? null);
    $credito = "{$autor} · {$licenca} · Wikimedia Commons";
    $pagina = 'https://commons.wikimedia.org/wiki/File:' . rawurlencode($arquivo);
    $up = $pdo->prepare('UPDATE cientistas SET foto = ?, foto_credito = ?, foto_fonte = ? WHERE id = ? AND foto IS NULL');
    $up->execute([$foto, $credito, $pagina, $id]);
    if ($up->rowCount()) {
        $ok++;
        @chown(FOTOS_DIR . '/' . $foto, 'www-data');
    } else {
        apagar_foto($foto);
    }
    sleep(2); // respeita o limite de requisições da Wikimedia
}
log_foto("{$ok} foto(s) importada(s).");
