<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#170b3b">
<title>Observatório Duília de Mello · <?= APP_NOME ?></title>
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="preload" href="/assets/fonts/archivo-var.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/archivo-condensada-black.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/css/estilo.css">
<link rel="stylesheet" href="/assets/css/jogo.css">
<link rel="stylesheet" href="/areas.css.php">
</head>
<body class="tela-jogo">
<?php sprites(); ?>
<div id="jogo"></div>

<div class="carregando" id="carregando">
  <?= simbolo('s-lume', 'carregando-lume') ?>
  <p>Abrindo a cúpula do observatório…</p>
  <noscript><p>O jogo precisa de JavaScript. Veja o <a href="/cientistas.php">álbum de estrelas</a>.</p></noscript>
</div>

<section class="capa" id="capa" hidden>
  <div class="capa-miolo">
    <p class="capa-sub">Observatório Duília de Mello</p>
    <h1 class="capa-titulo">Conste<em>lar</em></h1>
    <p class="capa-frase">As estrelas guardam histórias de cientistas brasileiras. Quase ninguém as conhece, e por isso o céu está apagado. <strong>Ouça cada história e acenda o céu.</strong></p>
    <div class="grupo-botoes capa-botoes">
      <button class="botao botao--claro botao--grande" id="capa-jogar">Começar</button>
      <button class="botao botao--tinta botao--grande" id="capa-novo" hidden>Novo jogo</button>
    </div>
    <ul class="capa-teclas" id="capa-teclas"></ul>
  </div>
</section>

<div class="hud" id="hud" hidden>
  <a class="hud-btn hud-sair vidro-escuro" href="/" aria-label="Voltar ao site">←<span>Constelar</span></a>
  <div class="hud-direita">
    <div class="hud-contador vidro-escuro" aria-live="polite"><?= simbolo('u-estrela', 'ic') ?><strong id="hud-num">0</strong><span id="hud-total">/0</span></div>
    <button class="hud-btn vidro-escuro" id="btn-mapa" title="Mapa do céu (M)" aria-label="Mapa do céu"><?= simbolo('u-mapa', 'ic') ?></button>
    <button class="hud-btn vidro-escuro" id="btn-album" title="Álbum (B)" aria-label="Álbum"><?= simbolo('u-album', 'ic') ?></button>
    <button class="hud-btn vidro-escuro" id="btn-menu" title="Menu (Esc)" aria-label="Menu"><?= simbolo('u-menu', 'ic') ?></button>
  </div>
</div>

<div class="local" id="local" aria-live="polite"></div>
<p class="dica vidro-escuro" id="dica" hidden></p>
<div class="toasts" id="toasts" role="status" aria-live="polite"></div>

<section class="dialogo vidro-escuro" id="dialogo" hidden aria-live="polite">
  <div class="dlg-retrato" id="dlg-retrato"></div>
  <p class="dlg-sub" id="dlg-sub"></p>
  <h2 class="dlg-titulo" id="dlg-titulo"></h2>
  <p class="dlg-corpo" id="dlg-corpo"></p>
  <div class="dlg-opcoes" id="dlg-opcoes"></div>
  <div class="dlg-rodape">
    <span class="dlg-pag" id="dlg-pag"></span>
    <button type="button" class="botao botao--roxo" id="dlg-btn" tabindex="-1">Continuar</button>
  </div>
</section>

<dialog class="painel" id="painel">
  <header class="painel-topo">
    <h2 class="painel-titulo" id="painel-titulo"></h2>
    <button class="hud-btn" id="painel-fechar" aria-label="Fechar"><?= simbolo('u-fechar', 'ic') ?></button>
  </header>
  <div class="painel-corpo" id="painel-corpo"></div>
</dialog>

<script src="/assets/js/phaser.min.js"></script>
<script type="module" src="/assets/js/jogo/main.js"></script>
</body>
</html>
