<?php
declare(strict_types=1);

/**
 * Aplica, em ordem, os arquivos de app/db/migracoes/*.sql que ainda não rodaram neste banco.
 * Roda na subida do container (docker/entrypoint.sh). Uso manual: php bin/migrar.php
 */

if (PHP_SAPI !== 'cli') {
    exit(1);
}

require __DIR__ . '/../src/banco.php';

function log_migracao(string $msg): void
{
    fwrite(STDOUT, "[constelar] {$msg}\n");
}

/** Divide um script em comandos, respeitando textos entre aspas e comentários "--". */
function dividir_sql(string $sql): array
{
    $comandos = [];
    $atual = '';
    $n = strlen($sql);
    $aspas = null;

    for ($i = 0; $i < $n; $i++) {
        $ch = $sql[$i];

        if ($aspas !== null) {
            $atual .= $ch;
            if ($ch === '\\' && $i + 1 < $n) {
                $atual .= $sql[++$i];
            } elseif ($ch === $aspas) {
                if ($i + 1 < $n && $sql[$i + 1] === $aspas) {
                    $atual .= $sql[++$i];
                } else {
                    $aspas = null;
                }
            }
            continue;
        }

        if ($ch === '-' && substr($sql, $i, 2) === '--') {
            $fim = strpos($sql, "\n", $i);
            $i = $fim === false ? $n : $fim;
            $atual .= "\n";
            continue;
        }
        if ($ch === "'" || $ch === '"' || $ch === '`') {
            $aspas = $ch;
            $atual .= $ch;
            continue;
        }
        if ($ch === ';') {
            if (trim($atual) !== '') {
                $comandos[] = trim($atual);
            }
            $atual = '';
            continue;
        }
        $atual .= $ch;
    }
    if (trim($atual) !== '') {
        $comandos[] = trim($atual);
    }
    return $comandos;
}

// 1. Espera o banco aceitar conexões (primeira inicialização pode demorar).
$pdo = null;
for ($tentativa = 1; ; $tentativa++) {
    try {
        $pdo = db();
        break;
    } catch (PDOException $e) {
        if ($tentativa >= 90) {
            fwrite(STDERR, "[constelar] Banco indisponível: {$e->getMessage()}\n");
            exit(1);
        }
        if ($tentativa === 1 || $tentativa % 10 === 0) {
            $alvo = (getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: 'db') . ':' . (getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: '3306');
            log_migracao("Aguardando o banco de dados em {$alvo}… ({$e->getMessage()})");
        }
        sleep(2);
    }
}

// 2. Uma instância por vez.
if ((int) $pdo->query("SELECT GET_LOCK('constelar_migracoes', 120)")->fetchColumn() !== 1) {
    fwrite(STDERR, "[constelar] Outra instância está migrando o banco há mais de 2 minutos.\n");
    exit(1);
}

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS migracoes (
        versao      VARCHAR(100) NOT NULL PRIMARY KEY,
        aplicada_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);

$aplicadas = $pdo->query('SELECT versao FROM migracoes')->fetchAll(PDO::FETCH_COLUMN);

// 3. Volume criado pelo protótipo (antes das migrações): o esquema inicial já existe.
if (!$aplicadas) {
    $temEsquema = (int) $pdo->query(
        "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'cientistas'"
    )->fetchColumn();
    if ($temEsquema) {
        $pdo->prepare('INSERT INTO migracoes (versao) VALUES (?)')->execute(['001_esquema_inicial']);
        $aplicadas[] = '001_esquema_inicial';
        log_migracao('Banco do protótipo detectado: esquema inicial marcado como aplicado.');
    }
}

// 4. Aplica o que falta.
$arquivos = glob(__DIR__ . '/../db/migracoes/*.sql') ?: [];
sort($arquivos, SORT_STRING);
$novas = 0;

foreach ($arquivos as $arquivo) {
    $versao = basename($arquivo, '.sql');
    if (in_array($versao, $aplicadas, true)) {
        continue;
    }
    log_migracao("Aplicando {$versao}…");
    $pdo->exec('SET NAMES utf8mb4');
    foreach (dividir_sql((string) file_get_contents($arquivo)) as $n => $comando) {
        try {
            $pdo->exec($comando);
        } catch (PDOException $e) {
            fwrite(STDERR, "[constelar] Falha em {$versao}, comando " . ($n + 1) . ": {$e->getMessage()}\n");
            fwrite(STDERR, '[constelar] ' . substr(preg_replace('/\s+/', ' ', $comando) ?? '', 0, 200) . "\n");
            exit(1);
        }
    }
    $pdo->prepare('INSERT INTO migracoes (versao) VALUES (?)')->execute([$versao]);
    $novas++;
}

$pdo->query("SELECT RELEASE_LOCK('constelar_migracoes')");
log_migracao($novas ? "Banco atualizado ({$novas} migração(ões))." : 'Banco em dia.');
