<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$total = (int) db()->query("SELECT COUNT(*) FROM cientistas WHERE status = 'aprovado' AND autorizacao_uso = 1")->fetchColumn();
$areas = db()->query('SELECT nome, cor FROM areas ORDER BY id')->fetchAll();

cabecalho('Início');
?>
<section class="hero">
  <svg class="ceu-hero" viewBox="0 0 1200 640" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
    <g class="tracos">
      <path d="M80 140L210 70L330 180L190 300L80 140"/>
      <path d="M880 90L1030 150L1130 70M1030 150L980 290L1120 360"/>
      <path d="M110 520L290 590L420 500"/>
      <path d="M820 560L960 500L1100 580"/>
    </g>
    <g class="nos">
      <circle cx="80" cy="140" r="5" fill="#ffd23f"/><circle cx="210" cy="70" r="7" fill="#b294ff"/>
      <circle cx="330" cy="180" r="4" fill="#ff7ac8"/><circle cx="190" cy="300" r="6" fill="#3ddc97"/>
      <circle cx="880" cy="90" r="5" fill="#ff7ac8"/><circle cx="1030" cy="150" r="7" fill="#ffd23f"/>
      <circle cx="1130" cy="70" r="4" fill="#b294ff"/><circle cx="980" cy="290" r="5" fill="#3ddc97"/>
      <circle cx="1120" cy="360" r="6" fill="#7b3fe4"/><circle cx="110" cy="520" r="6" fill="#7b3fe4"/>
      <circle cx="290" cy="590" r="4" fill="#ffd23f"/><circle cx="420" cy="500" r="5" fill="#ff7ac8"/>
      <circle cx="820" cy="560" r="5" fill="#b294ff"/><circle cx="960" cy="500" r="7" fill="#3ddc97"/>
      <circle cx="1100" cy="580" r="4" fill="#ffd23f"/>
    </g>
  </svg>

  <svg class="mascote" viewBox="0 0 120 130" aria-hidden="true">
    <path d="M60 6l15 32 35 5-25 24 6 35-31-17-31 17 6-35L10 43l35-5z" fill="#ffd23f" stroke="#7b3fe4" stroke-width="5" stroke-linejoin="round"/>
    <ellipse cx="49" cy="58" rx="7" ry="9" fill="#fff"/><ellipse cx="71" cy="58" rx="7" ry="9" fill="#fff"/>
    <circle cx="51" cy="60" r="3.5" fill="#1c0b45"/><circle cx="73" cy="60" r="3.5" fill="#1c0b45"/>
    <path d="M52 76q8 7 16 0" stroke="#1c0b45" stroke-width="3.5" fill="none" stroke-linecap="round"/>
  </svg>

  <p class="selo-topo glass glass--claro">✦ SNCT 2026 · mulheres na ciência</p>
  <h1 class="mega">
    <span class="linha">Cada cientista</span>
    <span class="linha">é uma <span class="contorno">estrela</span></span>
  </h1>
  <p class="hero-sub">Explore um observatório virtual, converse com cientistas brasileiras e acenda as constelações de cada área do conhecimento.</p>

  <div class="hero-acoes">
    <a class="botao glass glass--roxo glass--interativo" href="/jogo.php">Começar a explorar →</a>
    <a class="botao glass glass--interativo" href="/cientistas.php">Ver cientistas</a>
  </div>

  <ul class="chips" aria-label="Áreas do conhecimento">
    <?php foreach ($areas as $a): ?>
      <li class="glass glass--claro"><svg viewBox="0 0 10 10" width="10" height="10" aria-hidden="true"><circle cx="5" cy="5" r="5" fill="<?= e($a['cor']) ?>"/></svg><?= e($a['nome']) ?></li>
    <?php endforeach; ?>
  </ul>
  <p class="numeros"><strong><?= $total ?></strong> estrelas · <strong><?= count($areas) ?></strong> constelações</p>
</section>

<section class="secao" id="como">
  <p class="kicker">Como funciona</p>
  <h2 class="titulo-secao">Explore o céu.<br><em>Acenda as estrelas.</em></h2>
  <div class="passos">
    <article class="passo"><div class="num">01</div><h3>Explore</h3><p>Caminhe pelo observatório virtual com o teclado ou com toques na tela.</p></article>
    <article class="passo"><div class="num">02</div><h3>Converse</h3><p>Cada personagem representa uma cientista brasileira e conta a sua história.</p></article>
    <article class="passo"><div class="num">03</div><h3>Complete</h3><p>Acenda todas as estrelas de uma área para completar a constelação.</p></article>
  </div>
</section>

<section class="bloco-roxo">
  <div class="estrelas-fundo" aria-hidden="true"></div>
  <p class="kicker">Homenageada</p>
  <p class="citacao">Duília de Mello, a “Mulher das Estrelas”.</p>
  <p>Astrônoma brasileira que descobriu a supernova SN 1997D, colabora com a NASA e atua na divulgação científica para incentivar crianças e jovens a seguir carreira em ciência.</p>
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
