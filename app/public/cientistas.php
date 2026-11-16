<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$areas = areas();
$porArea = [];
foreach (cientistas_publicadas() as $c) {
    $porArea[$c['area_id']][] = $c;
}
$total = array_sum(array_map('count', $porArea));

cabecalho('Álbum de estrelas', [
    'descricao' => 'O álbum completo do Constelar: ' . $total . ' cientistas brasileiras em ' . count($areas) . ' constelações.',
]);
?>
<header class="cabeca-pagina">
  <p class="kicker">Todas as estrelas</p>
  <h1 class="titulo-pagina">O álbum <em>de estrelas</em></h1>
  <p class="lede"><?= $total ?> cientistas brasileiras em <?= count($areas) ?> constelações. No jogo, cada figurinha é conquistada quando você ouve a história da cientista.</p>
</header>

<nav class="album-nav vidro" aria-label="Constelações">
  <?php foreach ($areas as $a): ?>
    <a class="area-<?= $a['id'] ?>" href="#area-<?= $a['id'] ?>"><?= simbolo('i-' . $a['icone'], 'album-nav-ic') ?><?= e($a['nome']) ?><span><?= $a['total'] ?></span></a>
  <?php endforeach; ?>
</nav>

<?php foreach ($areas as $i => $a): $lista = $porArea[$a['id']] ?? []; ?>
<section class="album-secao area-<?= $a['id'] ?>" id="area-<?= $a['id'] ?>">
  <header class="album-cabeca">
    <div class="carta carta--fixa">
      <span class="carta-num">Carta nº <?= sprintf('%02d', $i + 1) ?></span>
      <?= figura_svg($a['icone'], count($lista), 'carta-figura', null, 'Desenho da ' . $a['constelacao']) ?>
    </div>
    <div class="album-titulo">
      <p class="kicker"><?= e($a['nome']) ?></p>
      <h2 class="titulo-secao"><?= e($a['constelacao']) ?></h2>
      <p><?= e($a['descricao']) ?></p>
    </div>
  </header>
  <?php if ($lista): ?>
    <div class="figurinhas">
      <?php foreach ($lista as $c): ?><?= figurinha($c, 'revelar') ?><?php endforeach; ?>
    </div>
  <?php else: ?>
    <p class="vazio">Esta constelação ainda espera suas primeiras estrelas. <a href="/cadastro-cientista.php">Indique uma cientista</a>.</p>
  <?php endif; ?>
</section>
<?php endforeach; ?>

<section class="secao secao--final">
  <h2 class="titulo-secao">Complete o álbum<br><em>jogando.</em></h2>
  <div class="grupo-botoes">
    <a class="botao botao--roxo botao--grande" href="/jogo.php">Abrir o observatório <?= simbolo('u-seta', 'ic') ?></a>
    <a class="botao botao--grande" href="/cadastro-cientista.php">Indicar cientista</a>
  </div>
</section>
<?php rodape();
