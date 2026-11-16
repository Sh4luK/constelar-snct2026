<?php
declare(strict_types=1);

const SESSAO_NOME = 'constelar';
const SESSAO_OCIOSA_SEG = 7200;

/** O visitante só recebe cookie quando precisa (login, formulários). */
function sessao_existe(): bool
{
    return isset($_COOKIE[SESSAO_NOME]);
}

function iniciar_sessao(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = ($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    session_name(SESSAO_NOME);
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => $https, 'path' => '/']);
    session_start();

    $agora = time();
    if (isset($_SESSION['visto']) && $agora - (int) $_SESSION['visto'] > SESSAO_OCIOSA_SEG) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
    $_SESSION['visto'] = $agora;
}

function encerrar_sessao(): void
{
    iniciar_sessao();
    $_SESSION = [];
    $p = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 3600, 'path' => $p['path'], 'secure' => $p['secure'],
        'httponly' => true, 'samesite' => $p['samesite'],
    ]);
    session_destroy();
}

/* ---------------------------------------------------------------- CSRF */

function csrf_token(): string
{
    iniciar_sessao();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function exigir_post_com_csrf(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit;
    }
    iniciar_sessao();
    $t = $_POST['csrf'] ?? '';
    if (!is_string($t) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $t)) {
        http_response_code(400);
        exit('Requisição inválida. Volte, recarregue a página e tente novamente.');
    }
}

/* ---------------------------------------------------------------- Autenticação */

function usuario_atual(): ?array
{
    if (session_status() !== PHP_SESSION_ACTIVE && !sessao_existe()) {
        return null;
    }
    iniciar_sessao();
    return $_SESSION['usuario'] ?? null;
}

function autenticar(array $u): void
{
    iniciar_sessao();
    session_regenerate_id(true);
    $_SESSION['usuario'] = ['id' => (int) $u['id'], 'nome' => (string) $u['nome'], 'papel' => (string) $u['papel']];
    unset($_SESSION['csrf']);
}

function exigir_login(): array
{
    $u = usuario_atual();
    if ($u === null) {
        redirecionar('/login.php?voltar=' . urlencode((string) ($_SERVER['REQUEST_URI'] ?? '/')));
    }
    return $u;
}

/** O papel é reconferido no banco a cada acesso: rebaixar um administrador vale na hora. */
function exigir_admin(): array
{
    $u = exigir_login();
    $st = db()->prepare('SELECT papel FROM usuarios WHERE id = ?');
    $st->execute([$u['id']]);
    if ($st->fetchColumn() !== 'admin') {
        http_response_code(403);
        require __DIR__ . '/../public/404.php';
        exit;
    }
    return $u;
}

/* ---------------------------------------------------------------- Limite de tentativas por IP */

function ip_binario(): string
{
    return inet_pton((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0')) ?: "\0\0\0\0";
}

function limite_excedido(string $acao, int $max, int $minutos): bool
{
    $st = db()->prepare(
        'SELECT COUNT(*) FROM tentativas_login WHERE acao = ? AND ip = ? AND criado_em > NOW() - INTERVAL ' . $minutos . ' MINUTE'
    );
    $st->execute([$acao, ip_binario()]);
    return (int) $st->fetchColumn() >= $max;
}

function registrar_tentativa(string $acao): void
{
    $pdo = db();
    $pdo->prepare('INSERT INTO tentativas_login (ip, acao) VALUES (?, ?)')->execute([ip_binario(), $acao]);
    if (random_int(1, 20) === 1) {
        $pdo->exec('DELETE FROM tentativas_login WHERE criado_em < NOW() - INTERVAL 1 DAY');
    }
}
