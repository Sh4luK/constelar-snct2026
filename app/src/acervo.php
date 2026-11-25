<?php
declare(strict_types=1);

/**
 * Consultas do acervo: busca com filtros, temas e estatísticas.
 * Tudo aqui considera apenas cientistas publicadas (FILTRO_PUBLICADA).
 */

const POR_PAGINA = 12;
const ORDENS = [
    'nome'     => ['Nome (A–Z)', 'c.nome ASC'],
    'area'     => ['Área do conhecimento', 'a.ordem ASC, a.id ASC, c.nome ASC'],
    'recentes' => ['Mais recentes', 'c.criado_em DESC, c.id DESC'],
];

/** Lê os filtros da URL (sempre validados). */
function filtros_da_url(array $q): array
{
    $texto = is_string($q['q'] ?? null) ? trim(mb_substr($q['q'], 0, 80)) : '';
    $ordem = is_string($q['ordem'] ?? null) && isset(ORDENS[$q['ordem']]) ? $q['ordem'] : 'nome';
    return [
        'q'      => $texto,
        'area'   => max(0, (int) ($q['area'] ?? 0)),
        'tema'   => max(0, (int) ($q['tema'] ?? 0)),
        'ordem'  => $ordem,
        'pagina' => max(1, (int) ($q['pagina'] ?? 1)),
    ];
}

/** URL do acervo com filtros (mescla alterações e remove vazios). */
function url_acervo(array $f, array $mudar = [], string $base = '/cientistas.php'): string
{
    $f = array_merge($f, $mudar);
    $limpo = [];
    foreach (['q', 'area', 'tema', 'ordem', 'pagina', 'vista'] as $k) {
        $v = $f[$k] ?? null;
        if ($v === null || $v === '' || $v === 0 || ($k === 'ordem' && $v === 'nome') || ($k === 'pagina' && (int) $v === 1) || ($k === 'vista' && $v === 'tabela')) {
            continue;
        }
        $limpo[$k] = $v;
    }
    return $base . ($limpo ? '?' . http_build_query($limpo) : '');
}

/**
 * Busca no acervo. Devolve ['total' => int, 'itens' => array].
 * $limite null = todos os resultados (exportação).
 */
function buscar_cientistas(array $f, ?int $limite = POR_PAGINA): array
{
    $where = [FILTRO_PUBLICADA];
    $params = [];

    if ($f['q'] !== '') {
        $like = '%' . addcslashes($f['q'], '%_\\') . '%';
        $where[] = '(c.nome LIKE ? OR c.instituicao LIKE ? OR c.biografia LIKE ? OR c.curiosidade LIKE ?)';
        array_push($params, $like, $like, $like, $like);
    }
    if ($f['area'] > 0) {
        $where[] = 'c.area_id = ?';
        $params[] = $f['area'];
    }
    if ($f['tema'] > 0) {
        $where[] = 'EXISTS (SELECT 1 FROM cientista_tema ct WHERE ct.cientista_id = c.id AND ct.tema_id = ?)';
        $params[] = $f['tema'];
    }
    $sqlWhere = implode(' AND ', $where);

    $st = db()->prepare("SELECT COUNT(*) FROM cientistas c JOIN areas a ON a.id = c.area_id WHERE {$sqlWhere}");
    $st->execute($params);
    $total = (int) $st->fetchColumn();

    $sql = "SELECT c.id, c.nome, c.area_id, c.instituicao, c.biografia, c.curiosidade, c.fonte, c.destaque, c.criado_em, c.foto, c.foto_credito,
                   a.nome AS area, a.constelacao, a.cor, a.icone
            FROM cientistas c JOIN areas a ON a.id = c.area_id
            WHERE {$sqlWhere}
            ORDER BY " . ORDENS[$f['ordem']][1];
    if ($limite !== null) {
        $sql .= ' LIMIT ' . (int) $limite . ' OFFSET ' . (int) (($f['pagina'] - 1) * $limite);
    }
    $st = db()->prepare($sql);
    $st->execute($params);
    $itens = $st->fetchAll();

    // Número no álbum, rosto da personagem e temas de cada resultado.
    $numeros = array_column(cientistas_publicadas(), 'numero', 'id');
    $temas = temas_por_cientista(array_map(fn ($c) => (int) $c['id'], $itens));
    foreach ($itens as &$c) {
        $c['id'] = (int) $c['id'];
        $c['area_id'] = (int) $c['area_id'];
        $c['destaque'] = (bool) $c['destaque'];
        $c['numero'] = $numeros[$c['id']] ?? 0;
        $c['variante'] = variante_personagem($c['id']);
        $c['temas'] = $temas[$c['id']] ?? [];
    }
    unset($c);

    return ['total' => $total, 'itens' => $itens];
}

/** Todos os temas, com o total de cientistas publicadas em cada um. */
function temas(): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = db()->query(
            'SELECT t.id, t.nome,
                    (SELECT COUNT(*) FROM cientista_tema ct JOIN cientistas c ON c.id = ct.cientista_id
                     WHERE ct.tema_id = t.id AND ' . FILTRO_PUBLICADA . ') AS total
             FROM temas t ORDER BY t.nome'
        )->fetchAll();
        foreach ($cache as &$t) {
            $t['id'] = (int) $t['id'];
            $t['total'] = (int) $t['total'];
        }
        unset($t);
    }
    return $cache;
}

/** [cientista_id => [[id, nome], …]] para os ids pedidos. */
function temas_por_cientista(array $ids): array
{
    if (!$ids) {
        return [];
    }
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $st = db()->prepare(
        "SELECT ct.cientista_id, t.id, t.nome FROM cientista_tema ct JOIN temas t ON t.id = ct.tema_id
         WHERE ct.cientista_id IN ({$ph}) ORDER BY t.nome"
    );
    $st->execute($ids);
    $r = [];
    foreach ($st->fetchAll() as $l) {
        $r[(int) $l['cientista_id']][] = ['id' => (int) $l['id'], 'nome' => $l['nome']];
    }
    return $r;
}

/** Substitui os temas de uma cientista: ids existentes + nomes novos (criados se preciso). */
function salvar_temas(int $cientistaId, array $ids, string $novos): void
{
    $pdo = db();
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn ($i) => $i > 0)));
    foreach (array_filter(array_map('trim', explode(',', $novos))) as $nome) {
        $nome = mb_substr(preg_replace('/\s+/u', ' ', $nome) ?? '', 0, 60);
        if (mb_strlen($nome) < 3) {
            continue;
        }
        $pdo->prepare('INSERT IGNORE INTO temas (nome) VALUES (?)')->execute([$nome]);
        $st = $pdo->prepare('SELECT id FROM temas WHERE nome = ?');
        $st->execute([$nome]);
        $ids[] = (int) $st->fetchColumn();
    }
    $pdo->prepare('DELETE FROM cientista_tema WHERE cientista_id = ?')->execute([$cientistaId]);
    $ins = $pdo->prepare('INSERT IGNORE INTO cientista_tema (cientista_id, tema_id) VALUES (?, ?)');
    foreach (array_unique($ids) as $t) {
        $ins->execute([$cientistaId, $t]);
    }
}

/** Números gerais do acervo para o painel de dados. */
function estatisticas(): array
{
    $pdo = db();
    $pub = FILTRO_PUBLICADA;
    $n = fn (string $sql) => (int) $pdo->query($sql)->fetchColumn();

    return [
        'cientistas'   => $n("SELECT COUNT(*) FROM cientistas c WHERE {$pub}"),
        'areas'        => $n('SELECT COUNT(*) FROM areas'),
        'temas'        => $n("SELECT COUNT(DISTINCT ct.tema_id) FROM cientista_tema ct JOIN cientistas c ON c.id = ct.cientista_id WHERE {$pub}"),
        'instituicoes' => $n("SELECT COUNT(DISTINCT c.instituicao) FROM cientistas c WHERE {$pub} AND c.instituicao IS NOT NULL"),
        'pendentes'    => $n("SELECT COUNT(*) FROM cientistas WHERE status = 'pendente'"),
        'indicacoes'   => $n('SELECT COUNT(*) FROM cientistas WHERE enviado_por IS NOT NULL'),
        'colaboradores'=> $n('SELECT COUNT(DISTINCT enviado_por) FROM cientistas WHERE enviado_por IS NOT NULL'),
        'por_status'   => $pdo->query('SELECT status, COUNT(*) FROM cientistas GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR),
        'instituicoes_top' => $pdo->query(
            "SELECT c.instituicao AS nome, COUNT(*) AS total FROM cientistas c
             WHERE {$pub} AND c.instituicao IS NOT NULL
             GROUP BY c.instituicao ORDER BY total DESC, nome ASC LIMIT 8"
        )->fetchAll(),
        'recentes' => $pdo->query(
            "SELECT c.id, c.nome, c.criado_em, a.nome AS area, c.area_id FROM cientistas c JOIN areas a ON a.id = c.area_id
             WHERE {$pub} ORDER BY c.criado_em DESC, c.id DESC LIMIT 5"
        )->fetchAll(),
    ];
}

/** Gráfico de barras horizontais em SVG (sem JS, compatível com a CSP). */
function grafico_barras(array $linhas, string $rotulo): string
{
    if (!$linhas) {
        return '<p class="vazio">Sem dados ainda.</p>';
    }
    $max = max(1, max(array_column($linhas, 'total')));
    $alt = 34;
    $h = count($linhas) * $alt;
    $o = '<svg class="grafico" viewBox="0 0 600 ' . $h . '" role="img" aria-label="' . e($rotulo) . '" preserveAspectRatio="xMinYMin meet">';
    foreach (array_values($linhas) as $i => $l) {
        $y = $i * $alt;
        $w = max(4, round(($l['total'] / $max) * 250));
        $classe = isset($l['area_id']) ? ' area-' . (int) $l['area_id'] : '';
        $o .= '<g class="grafico-linha' . $classe . '">';
        $o .= '<text class="grafico-rotulo" x="0" y="' . ($y + 22) . '">' . e(mb_strimwidth((string) $l['nome'], 0, 40, '…')) . '</text>';
        $o .= '<rect class="grafico-barra" x="300" y="' . ($y + 7) . '" width="' . $w . '" height="20" rx="4"/>';
        $o .= '<text class="grafico-valor" x="' . (308 + $w) . '" y="' . ($y + 22) . '">' . (int) $l['total'] . '</text>';
        $o .= '</g>';
    }
    return $o . '</svg>';
}
