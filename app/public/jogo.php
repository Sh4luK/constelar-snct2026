<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta name="theme-color" content="#050816">
<title>Observatório · Constelar</title>
<link rel="preload" href="/assets/fonts/archivo-var.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/css/estilo.css">
</head>
<body class="tela-jogo">
<div id="jogo"></div>

<a class="jogo-ui jogo-voltar glass glass--escuro glass--interativo" href="/">← Constelar</a>
<div class="jogo-ui hud glass glass--escuro" aria-live="polite">
  <strong id="hud-total">✦ 0 estrelas</strong>
  <ul id="hud-areas"></ul>
</div>
<p class="jogo-ui ajuda glass glass--escuro" id="ajuda" hidden></p>
<p class="jogo-ui toast glass glass--claro" id="toast" role="status" aria-live="polite"></p>
<section class="jogo-ui dialogo glass glass--escuro" id="dialogo" hidden aria-live="polite" aria-labelledby="dlg-titulo">
  <p class="dlg-sub" id="dlg-sub"></p>
  <h2 id="dlg-titulo"></h2>
  <p class="dlg-corpo" id="dlg-corpo"></p>
  <div class="dlg-rodape">
    <span class="dlg-pag" id="dlg-pag"></span>
    <button type="button" class="botao glass glass--azul glass--interativo" id="dlg-btn" tabindex="-1">Continuar →</button>
  </div>
</section>

<noscript>O jogo precisa de JavaScript. Veja a <a href="/cientistas.php">lista de cientistas</a>.</noscript>
<script src="/assets/js/phaser.min.js"></script>
<script src="/assets/js/glass.js"></script>
<script src="/assets/js/jogo.js"></script>
</body>
</html>
