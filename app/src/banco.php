<?php
declare(strict_types=1);

/** Conexão única por requisição. */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            getenv('DB_HOST') ?: 'db',
            getenv('DB_NAME') ?: 'constelar'
        );
        $pdo = new PDO($dsn, (string) getenv('DB_USER'), (string) getenv('DB_PASSWORD'), [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 5,
        ]);
    }
    return $pdo;
}

/** Regra única de publicação (LGPD): aprovada pela administração E com autorização registrada. */
const FILTRO_PUBLICADA = "c.status = 'aprovado' AND c.autorizacao_uso = 1";

/** Símbolos de constelação disponíveis (chaves de assets/dados/figuras.json). */
const ICONES_AREA = [
    'telescopio' => 'Telescópio',
    'atomo'      => 'Átomo',
    'celula'     => 'Célula',
    'frasco'     => 'Frasco',
    'pintura'    => 'Pintura rupestre',
    'compasso'   => 'Compasso',
    'estrela'    => 'Estrela',
];

/** Áreas do conhecimento (constelações), com o total de cientistas publicadas em cada uma. */
function areas(): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = db()->query(
            'SELECT a.id, a.nome, a.constelacao, a.descricao, a.cor, a.icone, a.ordem,
                    (SELECT COUNT(*) FROM cientistas c WHERE c.area_id = a.id AND ' . FILTRO_PUBLICADA . ') AS total
             FROM areas a
             ORDER BY a.ordem, a.id'
        )->fetchAll();
        foreach ($cache as &$a) {
            $a['id'] = (int) $a['id'];
            $a['total'] = (int) $a['total'];
            $a['ordem'] = (int) $a['ordem'];
        }
        unset($a);
    }
    return $cache;
}

function area_por_id(int $id): ?array
{
    foreach (areas() as $a) {
        if ($a['id'] === $id) {
            return $a;
        }
    }
    return null;
}

/**
 * Cientistas publicadas, na ordem do álbum (constelação, depois cadastro).
 * Cada linha ganha "numero" (posição no álbum) e "variante" (rosto da personagem).
 */
function cientistas_publicadas(): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = db()->query(
            'SELECT c.id, c.nome, c.area_id, c.instituicao, c.biografia, c.curiosidade, c.fonte, c.destaque, c.foto, c.foto_credito, c.foto_fonte,
                    a.nome AS area, a.constelacao, a.cor, a.icone
             FROM cientistas c
             JOIN areas a ON a.id = c.area_id
             WHERE ' . FILTRO_PUBLICADA . '
             ORDER BY a.ordem, a.id, c.id'
        )->fetchAll();
        foreach ($cache as $i => &$c) {
            $c['id'] = (int) $c['id'];
            $c['area_id'] = (int) $c['area_id'];
            $c['destaque'] = (bool) $c['destaque'];
            $c['numero'] = $i + 1;
            $c['variante'] = variante_personagem($c['id']);
        }
        unset($c);
    }
    return $cache;
}

function cientista_publicada(int $id): ?array
{
    foreach (cientistas_publicadas() as $c) {
        if ($c['id'] === $id) {
            return $c;
        }
    }
    return null;
}

/** Rosto da personagem (1 a 4), estável por cientista. O jogo usa a mesma regra. */
function variante_personagem(int $id): int
{
    return ($id * 7 + 3) % 4 + 1;
}
