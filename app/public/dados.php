<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$s = estatisticas();
$areas = areas();
$temas = array_values(array_filter(temas(), fn ($t) => $t['total'] > 0));
usort($temas, fn ($a, $b) => $b['total'] <=> $a['total'] ?: strcmp($a['nome'], $b['nome']));

$barrasAreas = array_map(fn ($a) => ['nome' => $a['nome'], 'total' => $a['total'], 'area_id' => $a['id']], $areas);
$barrasTemas = array_map(fn ($t) => ['nome' => $t['nome'], 'total' => $t['total']], $temas);

cabecalho('Dados do acervo', ['descricao' => 'Estatísticas do acervo do Constelar e dados abertos para download.']);
?>
<header class="cabeca-pagina cabeca-pagina--curta">
  <p class="kicker">Painel de dados</p>
  <h1 class="titulo-pagina">O acervo <em>em números</em></h1>
  <p class="lede">Números calculados em tempo real a partir do banco de dados. Só entram cientistas publicadas: aprovadas pela administração e com autorização registrada.</p>
</header>

<ul class="indicadores">
  <li class="cartao"><strong><?= $s['cientistas'] ?></strong><span>cientistas publicadas</span></li>
  <li class="cartao"><strong><?= $s['areas'] ?></strong><span>áreas do conhecimento</span></li>
  <li class="cartao"><strong><?= $s['temas'] ?></strong><span>temas de pesquisa</span></li>
  <li class="cartao"><strong><?= $s['instituicoes'] ?></strong><span>instituições citadas</span></li>
  <li class="cartao"><strong><?= $s['indicacoes'] ?></strong><span>indicações da comunidade</span></li>
  <li class="cartao"><strong><?= $s['pendentes'] ?></strong><span>indicações em revisão</span></li>
</ul>

<div class="paineis">
  <section class="cartao painel-dado">
    <h2 class="painel-titulo">Cientistas por área</h2>
    <?= grafico_barras($barrasAreas, 'Número de cientistas por área do conhecimento') ?>
  </section>
  <section class="cartao painel-dado">
    <h2 class="painel-titulo">Cientistas por tema</h2>
    <?= grafico_barras($barrasTemas, 'Número de cientistas por tema de pesquisa') ?>
  </section>
  <section class="cartao painel-dado">
    <h2 class="painel-titulo">Instituições mais citadas</h2>
    <?= grafico_barras($s['instituicoes_top'], 'Instituições com mais cientistas no acervo') ?>
  </section>
  <section class="cartao painel-dado">
    <h2 class="painel-titulo">Adicionadas recentemente</h2>
    <ol class="lista-recentes">
      <?php foreach ($s['recentes'] as $c): ?>
        <li class="area-<?= (int) $c['area_id'] ?>"><a href="/cientista.php?id=<?= (int) $c['id'] ?>"><?= e($c['nome']) ?></a><span class="miudo"><?= e($c['area']) ?> · <?= e(date('d/m/Y', strtotime($c['criado_em']))) ?></span></li>
      <?php endforeach; ?>
    </ol>
  </section>
</div>

<section class="secao secao--duas" id="dados-abertos">
  <div>
    <p class="kicker">Dados abertos</p>
    <h2 class="titulo-secao">Baixe e <em>reutilize</em></h2>
    <p>O acervo pode ser usado em trabalhos escolares, planilhas e outros projetos, sempre citando as fontes de cada cientista.</p>
  </div>
  <div class="downloads">
    <a class="cartao download" href="/exportar.php?formato=csv"><strong>Planilha CSV</strong><span>Abre no Excel, LibreOffice e Google Planilhas.</span></a>
    <a class="cartao download" href="/exportar.php?formato=json"><strong>Arquivo JSON</strong><span>Para programas e análises.</span></a>
    <a class="cartao download" href="/api/cientistas.php"><strong>API do jogo</strong><span><code>GET /api/cientistas.php</code>: áreas e cientistas publicadas.</span></a>
    <p class="miudo">Dica: no <a href="/cientistas.php">acervo</a>, os botões de exportar baixam só o resultado da busca atual.</p>
  </div>
</section>

<section class="secao secao--duas">
  <div>
    <p class="kicker">Como os dados são organizados</p>
    <h2 class="titulo-secao">O banco <em>por dentro</em></h2>
  </div>
  <div class="modelo">
    <div class="modelo-tabela"><strong>areas</strong><span>id, nome, constelação, cor, símbolo</span></div>
    <div class="modelo-liga">1 : N</div>
    <div class="modelo-tabela modelo-tabela--central"><strong>cientistas</strong><span>nome, biografia, instituição, fontes, status, autorização</span></div>
    <div class="modelo-liga">N : N</div>
    <div class="modelo-tabela"><strong>temas</strong><span>via <code>cientista_tema</code></span></div>
    <div class="modelo-liga modelo-liga--full">cada indicação guarda quem enviou (<code>usuarios</code>) e quem revisou</div>
  </div>
</section>
<?php rodape();
