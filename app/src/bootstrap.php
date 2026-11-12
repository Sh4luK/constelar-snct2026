<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', getenv('DB_HOST') ?: 'db', getenv('DB_NAME') ?: 'constelar');
        $pdo = new PDO($dsn, (string) getenv('DB_USER'), (string) getenv('DB_PASSWORD'), [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

function iniciar_sessao(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = ($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    session_name('constelar');
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => $https, 'path' => '/']);
    session_start();
}

function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

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
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit;
    }
    iniciar_sessao();
    $t = $_POST['csrf'] ?? '';
    if (!is_string($t) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $t)) {
        http_response_code(400);
        exit('Requisição inválida. Recarregue a página e tente novamente.');
    }
}

function usuario_atual(): ?array
{
    iniciar_sessao();
    return $_SESSION['usuario'] ?? null;
}

function exigir_login(): array
{
    $u = usuario_atual();
    if ($u === null) {
        redirecionar('/login.php?voltar=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'));
    }
    return $u;
}

function exigir_admin(): array
{
    $u = exigir_login();
    if ($u['papel'] !== 'admin') {
        http_response_code(403);
        exit('Acesso restrito ao administrador.');
    }
    return $u;
}

function autenticar(array $u): void
{
    iniciar_sessao();
    session_regenerate_id(true);
    $_SESSION['usuario'] = ['id' => (int) $u['id'], 'nome' => $u['nome'], 'papel' => $u['papel']];
    unset($_SESSION['csrf']);
}

function redirecionar(string $url): never
{
    // Só caminhos locais: evita open redirect.
    if (!str_starts_with($url, '/') || str_starts_with($url, '//') || str_contains($url, '\\')) {
        $url = '/';
    }
    header('Location: ' . $url, true, 303);
    exit;
}

function flash(?string $msg = null): ?string
{
    iniciar_sessao();
    if ($msg !== null) {
        $_SESSION['flash'] = $msg;
        return null;
    }
    $m = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $m;
}

function texto_post(string $campo, int $max): string
{
    $v = $_POST[$campo] ?? '';
    if (!is_string($v)) {
        return '';
    }
    $v = trim(preg_replace('/\s+/u', ' ', $v) ?? '');
    return mb_substr($v, 0, $max);
}

function cabecalho(string $titulo): void
{
    $u = usuario_atual();
    $f = flash();
    ?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titulo) ?> · Constelar</title>
<link rel="stylesheet" href="/assets/css/estilo.css">
</head>
<body>
<header class="topo">
  <a class="marca" href="/">✦ Constelar</a>
  <nav>
    <a href="/jogo.php">Jogar</a>
    <a href="/cientistas.php">Cientistas</a>
    <a href="/cadastro-cientista.php">Indicar cientista</a>
    <?php if ($u && $u['papel'] === 'admin'): ?><a href="/admin/">Painel</a><?php endif; ?>
    <?php if ($u): ?>
      <form method="post" action="/logout.php" class="inline"><?= csrf_campo() ?><button class="link">Sair (<?= e($u['nome']) ?>)</button></form>
    <?php else: ?>
      <a href="/login.php">Entrar</a>
    <?php endif; ?>
  </nav>
</header>
<main>
<?php if ($f): ?><p class="aviso"><?= e($f) ?></p><?php endif;
}

function rodape(): void
{
    ?></main>
<footer class="rodape">Constelar · SNCT 2026 · Em homenagem a Duília de Mello, a "Mulher das Estrelas".</footer>
</body>
</html>
<?php
}
