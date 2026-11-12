<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$total = (int) db()->query("SELECT COUNT(*) FROM cientistas WHERE status = 'aprovado' AND autorizacao_uso = 1")->fetchColumn();
$areas = db()->query('SELECT nome, cor FROM areas ORDER BY id')->fetchAll();

cabecalho('Início');
?>
<section class="hero">
  <svg class="ceu-hero" viewBox="0 0 1200 600" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
    <path d="M70 120L190 70L280 160L170 250ZM940 80L1060 140L1140 60M1060 140L1010 260L1130 320M90 480L230 540L330 470M880 520L990 470L1110 540"/>
    <g>
      <path d="M190 58l3 9 9 3-9 3-3 9-3-9-9-3 9-3z"/><path d="M1060 128l3 9 9 3-9 3-3 9-3-9-9-3 9-3z"/>
      <path d="M1010 248l3 9 9 3-9 3-3 9-3-9-9-3 9-3z"/><path d="M230 528l3 9 9 3-9 3-3 9-3-9-9-3 9-3z"/>
      <path d="M990 458l3 9 9 3-9 3-3 9-3-9-9-3 9-3z"/>
    </g>
  </svg>

  <span class="adesivo adesivo--vermelho ad1" aria-hidden="true">Ciência é<br>pra elas!</span>
  <span class="adesivo adesivo--amarelo ad2" aria-hidden="true">SNCT ★ 2026</span>
  <svg class="estouro" viewBox="0 0 120 120" aria-hidden="true">
    <polygon points="60.0,0.0 70.5,14.2 86.0,5.9 89.3,23.3 106.9,22.6 102.3,39.6 118.5,46.6 107.0,60.0 118.5,73.4 102.3,80.4 106.9,97.4 89.3,96.7 86.0,114.1 70.5,105.8 60.0,120.0 49.5,105.8 34.0,114.1 30.7,96.7 13.1,97.4 17.7,80.4 1.5,73.4 13.0,60.0 1.5,46.6 17.7,39.6 13.1,22.6 30.7,23.3 34.0,5.9 49.5,14.2"/>
    <text x="60" y="56" text-anchor="middle">100%</text>
    <text x="60" y="74" text-anchor="middle">BRASILEIRAS</text>
  </svg>
  <svg class="mascote" viewBox="0 0 120 120" aria-hidden="true">
    <path d="M60 6l15 32 35 5-25 24 6 35-31-17-31 17 6-35L10 43l35-5z" fill="#ffd23f" stroke="#141414" stroke-width="4" stroke-linejoin="round"/>
    <ellipse cx="49" cy="58" rx="7" ry="9" fill="#fff" stroke="#141414" stroke-width="2"/><ellipse cx="71" cy="58" rx="7" ry="9" fill="#fff" stroke="#141414" stroke-width="2"/>
    <circle cx="51" cy="60" r="3.5" fill="#141414"/><circle cx="73" cy="60" r="3.5" fill="#141414"/>
    <path d="M52 76q8 7 16 0" stroke="#141414" stroke-width="3.5" fill="none" stroke-linecap="round"/>
  </svg>

  <p class="hero-sub">Mulheres cientistas brasileiras<br>num observatório para explorar</p>
  <h1 class="mega">
    <span class="linha">Cada cientista</span>
    <span class="linha">é uma <em>estrela</em></span>
  </h1>

  <div class="hero-acoes">
    <a class="botao glass glass--tinta glass--interativo" href="/jogo.php">Começar a explorar →</a>
    <a class="botao glass glass--claro glass--interativo" href="/cientistas.php">Ver cientistas</a>
  </div>

  <ul class="faixa" aria-label="Áreas do conhecimento">
    <?php foreach ($areas as $a): ?>
      <li><svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true"><path fill="<?= e($a['cor']) ?>" stroke="#141414" stroke-width="2" stroke-linejoin="round" d="M12 2l2.9 6.9L22 10l-5.5 4.7L18.2 22 12 18.3 5.8 22l1.7-7.3L2 10l7.1-1.1z"/></svg><?= e($a['nome']) ?></li>
    <?php endforeach; ?>
  </ul>
</section>
<p class="numeros"><strong><?= $total ?></strong> estrelas acesas em <strong><?= count($areas) ?></strong> constelações</p>

<section class="secao" id="como">
  <p class="kicker">Como funciona</p>
  <h2 class="titulo-secao">Explore o céu.<br><em>Acenda as estrelas.</em></h2>
  <div class="passos">
    <article class="passo"><div class="num">01</div><h3>Explore</h3><p>Caminhe pelo observatório virtual com o teclado ou com toques na tela.</p></article>
    <article class="passo"><div class="num">02</div><h3>Converse</h3><p>Cada personagem representa uma cientista brasileira e conta a sua história.</p></article>
    <article class="passo"><div class="num">03</div><h3>Complete</h3><p>Acenda todas as estrelas de uma área para completar a constelação.</p></article>
  </div>
</section>

<section class="bloco-tinta">
  <span class="adesivo adesivo--rosa ad-sn" aria-hidden="true">★ SN 1997D</span>
  <p class="kicker">Homenageada</p>
  <p class="citacao">Duília de Mello, a <em>“Mulher das Estrelas”</em>.</p>
  <p>Astrônoma brasileira que descobriu a supernova SN 1997D, colabora com a NASA e atua na divulgação científica para incentivar crianças e jovens a seguir carreira em ciência.</p>
  <svg class="estrela-grande" viewBox="0 0 120 120" aria-hidden="true"><path d="M60 4l15 33 36 5-26 25 6 36-31-17-31 17 6-36L9 42l36-5z"/></svg>
</section>

<section class="secao">
  <p class="kicker">Participe</p>
  <h2 class="titulo-secao">Conhece uma cientista<br><em>que deveria estar aqui?</em></h2>
  <p>Crie uma conta e indique. Toda indicação passa pela revisão da administração, e só são publicadas cientistas que autorizaram o uso de seus dados (LGPD).</p>
  <div class="hero-acoes hero-acoes--esq">
    <a class="botao glass glass--roxo glass--interativo" href="/cadastro-cientista.php">Indicar cientista</a>
  </div>
</section>
<?php rodape();
