<?php
declare(strict_types=1);

/** Escapa texto para HTML. Toda saída de dados passa por aqui. */
function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Redireciona só para caminhos do próprio site (evita open redirect). */
function redirecionar(string $url): never
{
    if (!str_starts_with($url, '/') || str_starts_with($url, '//') || str_contains($url, '\\')) {
        $url = '/';
    }
    header('Location: ' . $url, true, 303);
    exit;
}

/** Mensagem exibida uma única vez na próxima página. Tipos: ok | erro. */
function flash(?string $msg = null, string $tipo = 'ok'): ?array
{
    if ($msg !== null) {
        iniciar_sessao();
        $_SESSION['flash'] = ['msg' => $msg, 'tipo' => $tipo];
        return null;
    }
    if (session_status() !== PHP_SESSION_ACTIVE && !sessao_existe()) {
        return null;
    }
    iniciar_sessao();
    $m = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($m) ? $m : null;
}

/** Campo de texto do POST: sem espaços duplicados, limitado em caracteres. */
function texto_post(string $campo, int $max): string
{
    $v = $_POST[$campo] ?? '';
    if (!is_string($v)) {
        return '';
    }
    $v = trim(preg_replace('/\s+/u', ' ', $v) ?? '');
    return mb_substr($v, 0, $max);
}

function senha_post(string $campo): string
{
    $v = $_POST[$campo] ?? '';
    return is_string($v) ? $v : '';
}

/** Primeira frase de um texto, para cartões e descrições. */
function primeira_frase(string $texto, int $max = 160): string
{
    if (preg_match('/^.+?[.!?](?=\s|$)/u', $texto, $m)) {
        $texto = $m[0];
    }
    if (mb_strlen($texto) > $max) {
        $texto = rtrim(mb_substr($texto, 0, $max - 1), " ,;:") . '…';
    }
    return $texto;
}

/** Responde 404 com a página de erro do site. */
function nao_encontrado(): never
{
    http_response_code(404);
    require __DIR__ . '/../public/404.php';
    exit;
}

/** Última barreira: nenhum detalhe interno vaza para o visitante. */
function tratar_erro_fatal(Throwable $e): void
{
    error_log('[constelar] ' . get_class($e) . ': ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
    if (headers_sent()) {
        return;
    }
    http_response_code($e instanceof PDOException ? 503 : 500);
    if (str_starts_with((string) ($_SERVER['REQUEST_URI'] ?? ''), '/api/')) {
        header('Content-Type: application/json; charset=utf-8');
        echo '{"erro":"indisponivel"}';
        return;
    }
    header('Content-Type: text/html; charset=utf-8');
    ?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Céu nublado · Constelar</title>
<link rel="stylesheet" href="/assets/css/estilo.css">
</head>
<body class="pagina-erro">
<main class="erro-central">
  <p class="kicker">Erro <?= http_response_code() ?></p>
  <h1 class="titulo-secao">Céu <em>nublado</em></h1>
  <p>O Constelar não conseguiu responder agora. Aguarde um instante e recarregue a página.</p>
  <p><a class="botao botao--tinta" href="/">Voltar ao início</a></p>
</main>
</body>
</html>
<?php
}
