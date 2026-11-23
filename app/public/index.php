<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$areas = areas();
$s = estatisticas();
$temas = array_values(array_filter(temas(), fn ($t) => $t['total'] > 0));
$homenageada = null;
foreach (cientistas_publicadas() as $c) {
    if ($c['destaque']) {
        $homenageada = $c;
        break;
    }
}
$recentes = buscar_cientistas(filtros_da_url(['ordem' => 'recentes']), 4)['itens'];

cabecalho('Acervo de mulheres cientistas brasileiras');
?>
<section class="hero hero--ferramenta">
  <svg class="ceu-hero" viewBox="0 0 1200 600" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
    <path class="ceu-tracos" pathLength="1" d="M70 120L190 70L280 160L170 250ZM940 80L1060 140L1140 60M1060 140L1010 260L1130 320M90 480L230 540L330 470M880 520L990 470L1110 540"/>
    <g class="ceu-brilhos">
      <path d="M190 58l3 9 9 3-9 3-3 9-3-9-9-3 9-3z"/><path d="M1060 128l3 9 9 3-9 3-3 9-3-9-9-3 9-3z"/>
      <path d="M1010 248l3 9 9 3-9 3-3 9-3-9-9-3 9-3z"/><path d="M230 528l3 9 9 3-9 3-3 9-3-9-9-3 9-3z"/>
    </g>
  </svg>
  <span class="adesivo adesivo--amarelo ad2" aria-hidden="true">SNCT ★ 2026</span>
  <?= simbolo('s-lume', 'mascote') ?>

  <p class="hero-sub">Acervo aberto e colaborativo</p>
  <h1 class="mega mega--ferramenta">
    <span class="linha"><span>Mulheres que fazem</span></span>
    <span class="linha"><span>a <em>ciência</em> brasileira</span></span>
  </h1>

  <form class="busca-hero vidro" method="get" action="/cientistas.php" role="search">
    <label class="sr" for="q-hero">Buscar no acervo</label>
    <input id="q-hero" type="search" name="q" placeholder="Busque por nome, instituição ou assunto…" maxlength="80">
    <select name="area" aria-label="Área">
      <option value="">Todas as áreas</option>
      <?php foreach ($areas as $a): ?><option value="<?= $a['id'] ?>"><?= e($a['nome']) ?></option><?php endforeach; ?>
    </select>
    <button class="botao botao--tinta">Buscar</button>
  </form>

  <ul class="hero-numeros">
    <li><strong><?= $s['cientistas'] ?></strong> cientistas</li>
    <li><strong><?= $s['areas'] ?></strong> áreas</li>
    <li><strong><?= $s['temas'] ?></strong> temas</li>
    <li><strong><?= $s['instituicoes'] ?></strong> instituições</li>
  </ul>
</section>

<section class="secao" id="areas">
  <div class="secao-cabeca">
    <div>
      <p class="kicker">Navegue pelo acervo</p>
      <h2 class="titulo-secao">Por área <em>do conhecimento</em></h2>
    </div>
    <a class="botao" href="/cientistas.php">Ver tudo em tabela <?= simbolo('u-seta', 'ic') ?></a>
  </div>
  <div class="areas-grade">
    <?php foreach ($areas as $a): ?>
      <a class="area-cartao area-<?= $a['id'] ?>" href="/cientistas.php?area=<?= $a['id'] ?>">
        <?= simbolo('i-' . $a['icone'], 'area-cartao-ic') ?>
        <span class="area-cartao-nome"><?= e($a['nome']) ?></span>
        <span class="area-cartao-total"><?= $a['total'] ?> <?= $a['total'] === 1 ? 'cientista' : 'cientistas' ?></span>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if ($temas): ?>
    <h3 class="sub-titulo">Por tema de pesquisa</h3>
    <p class="nuvem">
      <?php foreach ($temas as $t): ?><a class="chip chip--grande" href="/cientistas.php?tema=<?= $t['id'] ?>"><?= e($t['nome']) ?> <span><?= $t['total'] ?></span></a><?php endforeach; ?>
    </p>
  <?php endif; ?>
</section>

<?php if ($recentes): ?>
<section class="secao">
  <div class="secao-cabeca">
    <div>
      <p class="kicker">Últimas entradas</p>
      <h2 class="titulo-secao">Adicionadas <em>recentemente</em></h2>
    </div>
    <a class="botao" href="/cientistas.php?ordem=recentes">Mais recentes <?= simbolo('u-seta', 'ic') ?></a>
  </div>
  <div class="cartoes-acervo">
    <?php foreach ($recentes as $c): ?>
      <article class="cartao ficha-acervo area-<?= $c['area_id'] ?>">
        <span class="ficha-arte"><?= simbolo('s-ep-' . $c['variante'], 'ficha-personagem') ?></span>
        <div>
          <p class="miudo"><?= e($c['area']) ?></p>
          <h3 class="ficha-nome"><a href="/cientista.php?id=<?= $c['id'] ?>"><?= e($c['nome']) ?></a></h3>
          <?php if ($c['instituicao']): ?><p class="ficha-inst"><?= e($c['instituicao']) ?></p><?php endif; ?>
          <p><?= e(primeira_frase($c['biografia'], 150)) ?></p>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="secao" id="como">
  <p class="kicker">Como funciona</p>
  <h2 class="titulo-secao">Um banco de dados <em>colaborativo</em></h2>
  <ol class="fluxo">
    <li class="cartao"><span class="fluxo-num">1</span><h3>Pesquise</h3><p>Filtre por área, tema e instituição. Cada ficha traz biografia, curiosidade e fontes.</p></li>
    <li class="cartao"><span class="fluxo-num">2</span><h3>Indique</h3><p>Crie uma conta e cadastre uma cientista que ainda não está no acervo.</p></li>
    <li class="cartao"><span class="fluxo-num">3</span><h3>Revisão</h3><p>A administração confere as fontes e registra a autorização de uso dos dados (LGPD).</p></li>
    <li class="cartao"><span class="fluxo-num">4</span><h3>Publicação</h3><p>A cientista entra no acervo, nos dados abertos e ganha uma estrela no jogo.</p></li>
  </ol>
</section>

<section class="bloco-jogo revelar">
  <div class="bloco-texto">
    <p class="kicker">Modo jogo</p>
    <h2 class="citacao">Explore o acervo <em>como um céu</em>.</h2>
    <p>No Observatório Duília de Mello, cada cientista do banco de dados é uma estrela e cada área é uma constelação. Converse com as estrelas, complete constelações, encha o álbum de figurinhas e enfrente o desafio de perguntas.</p>
    <div class="grupo-botoes">
      <a class="botao botao--claro botao--grande" href="/jogo.php">Abrir o jogo <?= simbolo('u-seta', 'ic') ?></a>
      <a class="botao botao--tinta botao--grande" href="/cientistas.php?vista=album">Ver o álbum</a>
    </div>
  </div>
  <div class="bloco-jogo-arte" aria-hidden="true">
    <?= figura_svg('telescopio', 4, 'bloco-jogo-figura') ?>
    <?= simbolo('s-astronauta', 'bloco-jogo-astro') ?>
  </div>
</section>

<section class="bloco-tinta revelar">
  <span class="adesivo adesivo--rosa ad-sn" aria-hidden="true">★ SN 1997D</span>
  <div class="bloco-texto">
    <p class="kicker">Homenageada</p>
    <h2 class="citacao">Duília de Mello, a <em>“Mulher das Estrelas”</em>.</h2>
    <p>Astrônoma brasileira que descobriu a supernova SN 1997D, colabora com a NASA e atua na divulgação científica para incentivar crianças e jovens a seguir carreira em ciência.</p>
    <?php if ($homenageada): ?>
      <a class="botao botao--claro" href="/cientista.php?id=<?= $homenageada['id'] ?>">Ver a ficha <?= simbolo('u-seta', 'ic') ?></a>
    <?php endif; ?>
  </div>
  <?= simbolo('s-telescopio', 'bloco-desenho') ?>
</section>

<section class="secao secao--final secao--duas">
  <div>
    <p class="kicker">Participe</p>
    <h2 class="titulo-secao">Falta alguém <em>no acervo?</em></h2>
  </div>
  <div>
    <p class="lede">Indique uma cientista. Toda indicação passa pela revisão da administração, e só publicamos quem autorizou o uso dos dados.</p>
    <div class="grupo-botoes">
      <a class="botao botao--roxo botao--grande" href="/cadastro-cientista.php">Indicar cientista</a>
      <a class="botao botao--grande" href="/dados.php">Ver os dados</a>
    </div>
  </div>
</section>
<?php rodape();
