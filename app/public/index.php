<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$total = (int) db()->query("SELECT COUNT(*) FROM cientistas WHERE status = 'aprovado' AND autorizacao_uso = 1")->fetchColumn();
$areas = db()->query('SELECT nome, cor FROM areas ORDER BY id')->fetchAll();

cabecalho('Início');
?>
<section class="hero">
  <div class="hero-fundo" aria-hidden="true"></div>

  <span class="adesivo adesivo--vermelho a1" aria-hidden="true">Ciência é<br>pra elas!</span>
  <span class="adesivo adesivo--rosa a2" aria-hidden="true">★ Mulher das<br>estrelas</span>
  <span class="adesivo adesivo--verde a3" aria-hidden="true">SNCT<br>2026</span>

  <svg class="adesivo-svg mascote" viewBox="0 0 120 130" aria-hidden="true">
    <path d="M60 6l15 32 35 5-25 24 6 35-31-17-31 17 6-35L10 43l35-5z" fill="#ff7a1a" stroke="#fff" stroke-width="6" stroke-linejoin="round"/>
    <ellipse cx="49" cy="58" rx="8" ry="10" fill="#fff"/><ellipse cx="71" cy="58" rx="8" ry="10" fill="#fff"/>
    <circle cx="51" cy="60" r="4" fill="#111"/><circle cx="73" cy="60" r="4" fill="#111"/>
    <path d="M52 76q8 7 16 0" stroke="#111" stroke-width="3.5" fill="none" stroke-linecap="round"/>
    <path d="M42 100l-8 22h-10M78 100l8 22h10" stroke="#ff7a1a" stroke-width="6" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>

  <svg class="adesivo-svg selo" viewBox="0 0 140 140" aria-hidden="true">
    <defs><path id="circulo" d="M70 70m-48 0a48 48 0 1 1 96 0a48 48 0 1 1-96 0"/></defs>
    <circle cx="70" cy="70" r="66" fill="#a9b6ff" stroke="#fff" stroke-width="5"/>
    <text font-family="Archivo, sans-serif" font-weight="900" font-size="14" fill="#111">
      <textPath href="#circulo" textLength="298" lengthAdjust="spacing">EXPLORE • DESCUBRA • CONSTELE •</textPath>
    </text>
    <path d="M70 52l5 11 12 2-9 8 2 12-10-6-10 6 2-12-9-8 12-2z" fill="#1a33ff"/>
  </svg>

  <p class="hero-sub">Mulheres cientistas,<br>estrelas &amp; constelações</p>
  <h1 class="mega">
    <span class="linha">Cada cientista</span>
    <span class="linha">é uma <span class="serifa">estrela</span></span>
  </h1>
  <a class="ancora" href="#como">Descubra como <span class="seta">↓</span></a>

  <div class="hero-acoes">
    <a class="botao glass glass--azul glass--interativo" href="/jogo.php">Começar a explorar →</a>
    <a class="botao glass glass--interativo" href="/cientistas.php">Ver cientistas</a>
  </div>
  <p class="numeros"><strong><?= $total ?></strong> estrelas · <strong><?= count($areas) ?></strong> constelações</p>

  <ul class="faixa" aria-label="Áreas do conhecimento">
    <?php foreach ($areas as $a): ?>
      <li><svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="<?= e($a['cor']) ?>" stroke="#111" stroke-width="1.5" d="M12 2l2.9 6.9L22 10l-5.5 4.7L18.2 22 12 18.3 5.8 22l1.7-7.3L2 10l7.1-1.1z"/></svg><?= e($a['nome']) ?></li>
    <?php endforeach; ?>
  </ul>
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

<section class="bloco-azul">
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
    <a class="botao glass glass--azul glass--interativo" href="/cadastro-cientista.php">Indicar cientista</a>
  </div>
</section>
<?php rodape();
