<?php
declare(strict_types=1);

/** Conexão única por requisição. */
/** Endereço host:porta do banco configurado (para mensagens de log). */
function destino_banco(): string
{
    return $GLOBALS['__destino_banco'] ?? '?';
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        // Docker Compose usa DB_*; no Railway, o MySQL fornece MYSQL* (usados se DB_* faltar).
        // Também aceita um endereço completo: MYSQL_URL / DATABASE_URL = mysql://usuario:senha@host:porta/banco
        $url = parse_url((string) (getenv('MYSQL_URL') ?: getenv('DATABASE_URL') ?: '')) ?: [];
        $daUrl = [
            'MYSQLHOST' => $url['host'] ?? null, 'MYSQLPORT' => $url['port'] ?? null,
            'MYSQLUSER' => isset($url['user']) ? rawurldecode($url['user']) : null,
            'MYSQLPASSWORD' => isset($url['pass']) ? rawurldecode($url['pass']) : null,
            'MYSQLDATABASE' => isset($url['path']) ? ltrim($url['path'], '/') : null,
        ];
        // DB_* só valem como conjunto (Docker Compose, com DB_HOST). Sem DB_HOST, usa-se o banco da plataforma.
        $usarDb = (string) getenv('DB_HOST') !== '' || ((string) getenv('MYSQLHOST') === '' && !$url);
        $v = fn (string $a, string $b, string $padrao = '') => (string) (($usarDb ? getenv($a) : '') ?: getenv($b) ?: ($daUrl[$b] ?? null) ?: $padrao);
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $v('DB_HOST', 'MYSQLHOST', 'db'),
            (int) $v('DB_PORT', 'MYSQLPORT', '3306'),
            $v('DB_NAME', 'MYSQLDATABASE', 'constelar')
        );
        $GLOBALS['__destino_banco'] = $v('DB_HOST', 'MYSQLHOST', 'db') . ':' . $v('DB_PORT', 'MYSQLPORT', '3306');
        $pdo = new PDO($dsn, $v('DB_USER', 'MYSQLUSER'), $v('DB_PASSWORD', 'MYSQLPASSWORD'), [
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
