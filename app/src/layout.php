<?php
declare(strict_types=1);

/** Itens de navegação: [caminho, rótulo]. */
function links_nav(): array
{
    return [
        ['/cientistas.php', 'Acervo'],
        ['/dados.php', 'Dados'],
        ['/cadastro-cientista.php', 'Indicar'],
        ['/jogo.php', 'Jogo'],
        ['/sobre.php', 'Sobre'],
    ];
}

function links_conta(): array
{
    $u = usuario_atual();
    if (!$u) {
        return [['/login.php', 'Entrar']];
    }
    $l = [['/conta.php', 'Minha conta']];
    if ($u['papel'] === 'admin') {
        $l[] = ['/admin/', 'Painel'];
    }
    return $l;
}

function render_links(array $links, string $atual): void
{
    foreach ($links as [$href, $rotulo]) {
        $ativo = $atual === $href || ($href !== '/' && str_starts_with($atual, rtrim($href, '/') . '/'));
        echo '<a href="' . e($href) . '"' . ($ativo ? ' aria-current="page"' : '') . '>' . e($rotulo) . '</a>';
    }
}

function render_sair(): void
{
    if (usuario_atual()) {
        echo '<form method="post" action="/logout.php">' . csrf_campo() . '<button type="submit">Sair</button></form>';
    }
}

/**
 * Abre a página. Opções:
 *   descricao  meta description
 *   corpo      classes do <body>
 *   privado    true = página fora dos buscadores
 */
function cabecalho(string $titulo, array $op = []): void
{
    $f = flash();
    $atual = str_replace('index.php', '', (string) ($_SERVER['SCRIPT_NAME'] ?? '/'));
    $descricao = $op['descricao'] ?? 'Acervo pesquisável de mulheres cientistas brasileiras, com banco de dados colaborativo, dados abertos e um jogo para explorar o conteúdo.';
    ?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#6a2cff">
<meta name="description" content="<?= e($descricao) ?>">
<?php if (!empty($op['privado'])): ?><meta name="robots" content="noindex">
<?php endif; ?>
<title><?= e($titulo) ?> · <?= APP_NOME ?></title>
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="preload" href="/assets/fonts/archivo-var.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/css/estilo.css">
<link rel="stylesheet" href="/areas.css.php">
<script src="/assets/js/site.js" defer></script>
</head>
<body class="<?= e($op['corpo'] ?? '') ?>">
<?php sprites(); ?>
<a class="pular" href="#conteudo">Pular para o conteúdo</a>
<header class="topo">
  <a class="marca vidro" href="/" aria-label="Constelar: início"><?= simbolo('u-estrela', 'marca-estrela') ?>Constelar</a>
  <nav class="nav-pilula vidro" aria-label="Principal">
    <?php render_links(links_nav(), $atual); ?>
    <span class="nav-sep" aria-hidden="true"></span>
    <?php render_links(links_conta(), $atual); render_sair(); ?>
  </nav>
  <div class="topo-acoes">
    <form class="busca-topo" method="get" action="/cientistas.php" role="search">
      <input type="search" name="q" placeholder="Buscar no acervo" aria-label="Buscar no acervo" maxlength="80">
    </form>
    <details class="menu">
      <summary class="vidro">Menu</summary>
      <nav class="menu-painel vidro" aria-label="Menu">
        <?php render_links(links_nav(), $atual); render_links(links_conta(), $atual); render_sair(); ?>
      </nav>
    </details>
  </div>
</header>
<main id="conteudo" class="envolve">
<?php if ($f): ?><p class="aviso aviso--<?= e($f['tipo']) ?>" role="status"><?= e($f['msg']) ?></p><?php endif;
}

function rodape(): void
{
    $u = usuario_atual();
    ?></main>
<footer class="rodape">
  <p class="gigante" aria-hidden="true">Constelar<?= simbolo('u-estrela', 'gigante-estrela') ?></p>
  <div class="rodape-cols">
    <p class="rodape-frase">Acervo aberto e colaborativo de mulheres que fazem a ciência brasileira, com um jogo para explorá-lo. Em homenagem a Duília de Mello, a “Mulher das Estrelas”.</p>
    <nav aria-label="Rodapé">
      <a href="/cientistas.php">Acervo</a>
      <a href="/dados.php">Dados e exportação</a>
      <a href="/jogo.php">Jogo: Observatório</a>
      <a href="/cientistas.php?vista=album">Álbum de figurinhas</a>
      <a href="/cadastro-cientista.php">Indicar cientista</a>
      <a href="/sobre.php">Sobre o projeto</a>
      <a href="/privacidade.php">Privacidade e LGPD</a>
      <?php if ($u && $u['papel'] === 'admin'): ?><a href="/admin/">Painel</a><?php endif; ?>
    </nav>
  </div>
  <p class="rodape-linha"><span>SNCT 2026</span><span>Feito com PHP, MySQL e Phaser</span></p>
</footer>
</body>
</html>
<?php
}

/** Erros de validação de um formulário. */
function lista_erros(array $erros): void
{
    if (!$erros) {
        return;
    }
    echo '<div class="aviso aviso--erro" role="alert"><ul>';
    foreach ($erros as $er) {
        echo '<li>' . e($er) . '</li>';
    }
    echo '</ul></div>';
}
