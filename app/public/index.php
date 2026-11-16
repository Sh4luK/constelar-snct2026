<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$areas = areas();
$cientistas = cientistas_publicadas();
$total = count($cientistas);
$homenageada = null;
foreach ($cientistas as $c) {
    if ($c['destaque']) {
        $homenageada = $c;
        break;
    }
}

// Vitrine do álbum: uma figurinha por constelação, depois completa até 8.
$vitrine = [];
$usadas = [];
foreach ($cientistas as $c) {
    if (!isset($usadas[$c['area_id']])) {
        $usadas[$c['area_id']] = true;
        $vitrine[$c['id']] = $c;
    }
}
foreach ($cientistas as $c) {
    if (count($vitrine) >= 8) {
        break;
    }
    $vitrine[$c['id']] ??= $c;
}

cabecalho('Cada cientista é uma estrela');
?>
<section class="hero">
  <svg class="ceu-hero" viewBox="0 0 1200 600" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
    <path class="ceu-tracos" pathLength="1" d="M70 120L190 70L280 160L170 250ZM940 80L1060 140L1140 60M1060 140L1010 260L1130 320M90 480L230 540L330 470M880 520L990 470L1110 540"/>
    <g class="ceu-brilhos">
      <path d="M190 58l3 9 9 3-9 3-3 9-3-9-9-3 9-3z"/><path d="M1060 128l3 9 9 3-9 3-3 9-3-9-9-3 9-3z"/>
      <path d="M1010 248l3 9 9 3-9 3-3 9-3-9-9-3 9-3z"/><path d="M230 528l3 9 9 3-9 3-3 9-3-9-9-3 9-3z"/>
      <path d="M990 458l3 9 9 3-9 3-3 9-3-9-9-3 9-3z"/><path d="M70 108l3 9 9 3-9 3-3 9-3-9-9-3 9-3z"/>
    </g>
  </svg>

  <span class="adesivo adesivo--vermelho ad1" aria-hidden="true">Ciência é<br>pra elas!</span>
  <span class="adesivo adesivo--amarelo ad2" aria-hidden="true">SNCT ★ 2026</span>
  <svg class="estouro" viewBox="0 0 120 120" aria-hidden="true">
    <polygon points="60.0,0.0 70.5,14.2 86.0,5.9 89.3,23.3 106.9,22.6 102.3,39.6 118.5,46.6 107.0,60.0 118.5,73.4 102.3,80.4 106.9,97.4 89.3,96.7 86.0,114.1 70.5,105.8 60.0,120.0 49.5,105.8 34.0,114.1 30.7,96.7 13.1,97.4 17.7,80.4 1.5,73.4 13.0,60.0 1.5,46.6 17.7,39.6 13.1,22.6 30.7,23.3 34.0,5.9 49.5,14.2"/>
    <text class="estouro-num" x="60" y="62" text-anchor="middle"><?= $total ?></text>
    <text class="estouro-rot" x="60" y="80" text-anchor="middle">ESTRELAS</text>
  </svg>
  <?= simbolo('s-lume', 'mascote') ?>

  <p class="hero-sub">Um jogo para conhecer as mulheres<br>que fazem a ciência brasileira</p>
  <h1 class="mega">
    <span class="linha"><span>Cada cientista</span></span>
    <span class="linha"><span>é uma <em>estrela</em></span></span>
  </h1>

  <div class="hero-acoes">
    <a class="botao botao--tinta botao--grande" href="/jogo.php">Começar a explorar <?= simbolo('u-seta', 'ic') ?></a>
    <a class="botao botao--claro botao--grande" href="/cientistas.php">Ver o álbum</a>
  </div>

  <ul class="faixa" aria-label="Constelações">
    <?php foreach ($areas as $a): ?>
      <li class="area-<?= $a['id'] ?>"><?= simbolo('i-' . $a['icone'], 'faixa-ic') ?><?= e($a['nome']) ?></li>
    <?php endforeach; ?>
  </ul>
</section>

<?php if ($cientistas): ?>
<div class="letreiro" aria-hidden="true">
  <div class="letreiro-trilho">
    <?php for ($v = 0; $v < 2; $v++): foreach ($cientistas as $i => $c): ?>
      <span class="<?= $i % 2 ? 'vazado' : '' ?>"><?= e($c['nome']) ?></span><?= simbolo('u-estrela', 'letreiro-estrela') ?>
    <?php endforeach; endfor; ?>
  </div>
</div>
<?php endif; ?>

<section class="secao" id="como">
  <p class="kicker">Como funciona</p>
  <h2 class="titulo-secao">Explore o céu.<br><em>Acenda as estrelas.</em></h2>
  <ol class="passos">
    <li class="passo revelar">
      <span class="passo-arte passo-arte--roxo"><?= simbolo('s-astronauta', 'passo-desenho') ?></span>
      <span class="passo-num">01</span>
      <h3>Explore</h3>
      <p>Caminhe pelo Observatório Duília de Mello com o teclado, o mouse ou tocando na tela.</p>
    </li>
    <li class="passo revelar">
      <span class="passo-arte passo-arte--amarelo area-0"><?= simbolo('s-ep-2', 'passo-desenho') ?></span>
      <span class="passo-num">02</span>
      <h3>Converse</h3>
      <p>Cada estrela guarda a história de uma cientista brasileira. Ouça a história para acendê-la.</p>
    </li>
    <li class="passo revelar">
      <span class="passo-arte passo-arte--tinta area-0"><?= figura_svg('telescopio', 4, 'passo-figura') ?></span>
      <span class="passo-num">03</span>
      <h3>Complete</h3>
      <p>Acenda todas as estrelas de uma área para completar a constelação e encher o seu álbum.</p>
    </li>
  </ol>
</section>

<section class="secao" id="constelacoes">
  <p class="kicker">O mapa do céu</p>
  <h2 class="titulo-secao"><?= count($areas) ?> constelações,<br><em>uma para cada área.</em></h2>
  <div class="cartas">
    <?php foreach ($areas as $i => $a): ?>
      <a class="carta area-<?= $a['id'] ?> revelar" href="/cientistas.php#area-<?= $a['id'] ?>">
        <span class="carta-num">Carta nº <?= sprintf('%02d', $i + 1) ?></span>
        <?= figura_svg($a['icone'], $a['total'], 'carta-figura') ?>
        <span class="carta-pe">
          <strong class="carta-nome"><?= e($a['constelacao']) ?></strong>
          <span class="carta-area"><?= e($a['nome']) ?></span>
        </span>
        <span class="carta-total"><?= $a['total'] ?> <?= $a['total'] === 1 ? 'estrela' : 'estrelas' ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<?php if ($vitrine): ?>
<section class="secao" id="album">
  <div class="secao-cabeca">
    <div>
      <p class="kicker">O álbum</p>
      <h2 class="titulo-secao">Uma figurinha<br><em>para cada cientista.</em></h2>
    </div>
    <a class="botao" href="/cientistas.php">Ver as <?= $total ?> figurinhas <?= simbolo('u-seta', 'ic') ?></a>
  </div>
  <div class="vitrine">
    <?php foreach ($vitrine as $c): ?><?= figurinha($c) ?><?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="bloco-tinta revelar">
  <span class="adesivo adesivo--rosa ad-sn" aria-hidden="true">★ SN 1997D</span>
  <div class="bloco-texto">
    <p class="kicker">Homenageada</p>
    <h2 class="citacao">Duília de Mello, a <em>“Mulher das Estrelas”</em>.</h2>
    <p>Astrônoma brasileira que descobriu a supernova SN 1997D, colabora com a NASA e atua na divulgação científica para incentivar crianças e jovens a seguir carreira em ciência. O observatório do jogo leva o seu nome.</p>
    <?php if ($homenageada): ?>
      <a class="botao botao--claro" href="/cientista.php?id=<?= $homenageada['id'] ?>">Conhecer a história <?= simbolo('u-seta', 'ic') ?></a>
    <?php endif; ?>
  </div>
  <?= simbolo('s-telescopio', 'bloco-desenho') ?>
</section>

<section class="secao secao--final">
  <p class="kicker">Participe</p>
  <h2 class="titulo-secao">Conhece uma cientista<br><em>que deveria estar aqui?</em></h2>
  <p class="lede">Crie uma conta e indique. Toda indicação passa pela revisão da administração, e só publicamos cientistas que autorizaram o uso de seus dados.</p>
  <div class="grupo-botoes">
    <a class="botao botao--roxo botao--grande" href="/cadastro-cientista.php">Indicar cientista</a>
    <a class="botao botao--grande" href="/sobre.php">Sobre o projeto</a>
  </div>
</section>
<?php rodape();
