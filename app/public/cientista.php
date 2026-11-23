<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$c = cientista_publicada((int) ($_GET['id'] ?? 0));
if ($c === null) {
    nao_encontrado();
}

$todas = cientistas_publicadas();
$vizinhas = array_values(array_filter($todas, fn ($x) => $x['area_id'] === $c['area_id']));
$indiceNaArea = array_search($c['id'], array_column($vizinhas, 'id'), true);
$pos = $c['numero'] - 1;
$anterior = $todas[$pos - 1] ?? null;
$proxima = $todas[$pos + 1] ?? null;
$outras = array_values(array_filter($vizinhas, fn ($x) => $x['id'] !== $c['id']));
$temasDela = temas_por_cientista([$c['id']])[$c['id']] ?? [];

cabecalho($c['nome'], ['descricao' => primeira_frase($c['biografia'])]);
?>
<article class="perfil area-<?= $c['area_id'] ?>">
  <div class="perfil-lado">
    <div class="perfil-figurinha">
      <span class="fig-num" aria-hidden="true"><?= sprintf('%02d', $c['numero']) ?></span>
      <?php if ($c['destaque']): ?><span class="fig-selo">Homenageada</span><?php endif; ?>
      <span class="fig-arte"><?= simbolo('u-estrela', 'fig-brilho') ?><?= simbolo('s-ep-' . $c['variante'], 'fig-personagem') ?></span>
    </div>
    <div class="carta carta--mini">
      <?= figura_svg($c['icone'], count($vizinhas), 'carta-figura', $indiceNaArea === false ? null : (int) $indiceNaArea) ?>
      <span class="carta-pe">
        <strong class="carta-nome"><?= e($c['constelacao']) ?></strong>
        <span class="carta-area">a estrela dela está em destaque</span>
      </span>
    </div>
  </div>

  <div class="perfil-texto">
    <p class="kicker"><a href="/cientistas.php#area-<?= $c['area_id'] ?>"><?= e($c['constelacao']) ?></a> · <?= e($c['area']) ?></p>
    <h1 class="titulo-pagina titulo-pagina--nome"><?= e($c['nome']) ?></h1>
    <?php if ($c['instituicao']): ?><p class="perfil-inst"><?= e($c['instituicao']) ?></p><?php endif; ?>
    <?php if ($temasDela): ?>
      <p class="perfil-temas"><?php foreach ($temasDela as $t): ?><a class="chip" href="/cientistas.php?tema=<?= $t['id'] ?>"><?= e($t['nome']) ?></a><?php endforeach; ?></p>
    <?php endif; ?>

    <p class="perfil-bio"><?= e($c['biografia']) ?></p>

    <?php if ($c['curiosidade']): ?>
      <aside class="nota">
        <span class="nota-rotulo">Você sabia?</span>
        <p><?= e($c['curiosidade']) ?></p>
      </aside>
    <?php endif; ?>

    <p class="fontes"><strong>Fontes</strong> <?= e($c['fonte']) ?></p>

    <div class="grupo-botoes">
      <a class="botao botao--roxo" href="/cientistas.php?area=<?= $c['area_id'] ?>">Mais de <?= e($c['area']) ?> <?= simbolo('u-seta', 'ic') ?></a>
      <a class="botao" href="/jogo.php">Encontrar no jogo</a>
    </div>
  </div>
</article>

<nav class="perfil-nav" aria-label="Outras cientistas">
  <?php if ($anterior): ?>
    <a class="perfil-nav-item" href="/cientista.php?id=<?= $anterior['id'] ?>"><span>← Nº <?= sprintf('%02d', $anterior['numero']) ?></span><strong><?= e($anterior['nome']) ?></strong></a>
  <?php else: ?><span></span><?php endif; ?>
  <?php if ($proxima): ?>
    <a class="perfil-nav-item perfil-nav-item--dir" href="/cientista.php?id=<?= $proxima['id'] ?>"><span>Nº <?= sprintf('%02d', $proxima['numero']) ?> →</span><strong><?= e($proxima['nome']) ?></strong></a>
  <?php endif; ?>
</nav>

<?php if ($outras): ?>
<section class="secao">
  <p class="kicker">Na mesma constelação</p>
  <h2 class="titulo-secao"><?= e($c['constelacao']) ?></h2>
  <div class="figurinhas">
    <?php foreach ($outras as $o): ?><?= figurinha($o) ?><?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
<?php rodape();
