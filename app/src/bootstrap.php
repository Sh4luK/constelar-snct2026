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

function links_nav(): array
{
    $u = usuario_atual();
    $links = [['/jogo.php', 'Jogar'], ['/cientistas.php', 'Cientistas'], ['/cadastro-cientista.php', 'Indicar']];
    if ($u && $u['papel'] === 'admin') {
        $links[] = ['/admin/', 'Painel'];
    }
    if (!$u) {
        $links[] = ['/login.php', 'Entrar'];
    }
    return $links;
}

function render_links(string $atual): void
{
    foreach (links_nav() as [$href, $rotulo]) {
        $ativo = rtrim($atual, '/') === rtrim(str_replace('index.php', '', $href), '/') || $atual === $href;
        echo '<a href="' . e($href) . '"' . ($ativo ? ' aria-current="page"' : '') . '>' . e($rotulo) . '</a>';
    }
    if (usuario_atual()) {
        echo '<form method="post" action="/logout.php">' . csrf_campo() . '<button>Sair</button></form>';
    }
}

function cabecalho(string $titulo): void
{
    $f = flash();
    $atual = str_replace('index.php', '', (string) ($_SERVER['SCRIPT_NAME'] ?? '/'));
    ?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#f2efe7">
<title><?= e($titulo) ?> · Constelar</title>
<link rel="preload" href="/assets/fonts/archivo-var.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/css/estilo.css">
<script src="/assets/js/glass.js" defer></script>
</head>
<body>
<div class="moldura">
<header class="topo">
  <a class="marca glass glass--claro glass--interativo" href="/"><span aria-hidden="true">✦</span> constelar</a>
  <nav class="nav-pilula glass" aria-label="Principal"><?php render_links($atual); ?></nav>
  <div class="topo-acoes">
    <a class="botao-cta glass glass--roxo glass--interativo" href="/jogo.php"><span class="ponto"></span>Explorar</a>
    <details class="menu">
      <summary class="glass glass--interativo">Menu</summary>
      <nav class="menu-painel glass glass--claro" aria-label="Menu"><?php render_links($atual); ?></nav>
    </details>
  </div>
</header>
<main>
<?php if ($f): ?><p class="aviso" role="status"><?= e($f) ?></p><?php endif;
}

function rodape(): void
{
    ?></main>
</div>
<footer class="rodape">
  <p class="gigante">constelar<span aria-hidden="true">✦</span></p>
  <p>SNCT 2026 · Em homenagem a Duília de Mello, a "Mulher das Estrelas". Só exibimos cientistas que autorizaram o uso de seus dados (LGPD).</p>
</footer>
</body>
</html>
<?php
}
