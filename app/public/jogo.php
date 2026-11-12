<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<title>Observatório · Constelar</title>
<link rel="stylesheet" href="/assets/css/estilo.css">
</head>
<body class="tela-jogo">
<a class="voltar" href="/">← Constelar</a>
<div id="jogo"></div>
<noscript>O jogo precisa de JavaScript. Veja a <a href="/cientistas.php">lista de cientistas</a>.</noscript>
<script src="/assets/js/phaser.min.js"></script>
<script src="/assets/js/jogo.js"></script>
</body>
</html>
