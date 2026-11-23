<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$f = filtros_da_url($_GET);
$vista = in_array($_GET['vista'] ?? '', ['cartoes', 'album'], true) ? $_GET['vista'] : 'tabela';
$f['vista'] = $vista;
$areas = areas();
$temas = array_values(array_filter(temas(), fn ($t) => $t['total'] > 0));
$filtrando = $f['q'] !== '' || $f['area'] || $f['tema'];

if ($vista === 'album') {
    $porArea = [];
    foreach (cientistas_publicadas() as $c) {
        $porArea[$c['area_id']][] = $c;
    }
} else {
    $r = buscar_cientistas($f);
    $paginas = max(1, (int) ceil($r['total'] / POR_PAGINA));
    if ($f['pagina'] > $paginas) {
        redirecionar(url_acervo($f, ['pagina' => $paginas]));
    }
}

$nomeArea = $f['area'] ? (area_por_id($f['area'])['nome'] ?? '') : '';
$nomeTema = '';
foreach ($temas as $t) {
    if ($t['id'] === $f['tema']) {
        $nomeTema = $t['nome'];
    }
}

cabecalho('Acervo', ['descricao' => 'Busque mulheres cientistas brasileiras por nome, área, tema ou instituição.']);
?>
<header class="cabeca-pagina cabeca-pagina--curta">
  <p class="kicker">Banco de dados</p>
  <h1 class="titulo-pagina">O <em>acervo</em></h1>
  <p class="lede">Busque por nome, instituição ou palavra da biografia. Combine com área e tema, ordene e exporte o resultado.</p>
</header>

<form class="filtros cartao" method="get" action="/cientistas.php" role="search">
  <label class="campo filtros-busca">Buscar
    <input type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Ex.: Duília, USP, vulcões, polímeros…" maxlength="80">
  </label>
  <label class="campo">Área
    <select name="area">
      <option value="">Todas</option>
      <?php foreach ($areas as $a): ?>
        <option value="<?= $a['id'] ?>" <?= $a['id'] === $f['area'] ? 'selected' : '' ?>><?= e($a['nome']) ?> (<?= $a['total'] ?>)</option>
      <?php endforeach; ?>
    </select>
  </label>
  <label class="campo">Tema
    <select name="tema">
      <option value="">Todos</option>
      <?php foreach ($temas as $t): ?>
        <option value="<?= $t['id'] ?>" <?= $t['id'] === $f['tema'] ? 'selected' : '' ?>><?= e($t['nome']) ?> (<?= $t['total'] ?>)</option>
      <?php endforeach; ?>
    </select>
  </label>
  <label class="campo">Ordenar
    <select name="ordem">
      <?php foreach (ORDENS as $k => [$rotulo]): ?>
        <option value="<?= $k ?>" <?= $k === $f['ordem'] ? 'selected' : '' ?>><?= e($rotulo) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <?php if ($vista !== 'tabela'): ?><input type="hidden" name="vista" value="<?= e($vista === 'album' ? 'cartoes' : $vista) ?>"><?php endif; ?>
  <div class="filtros-acoes">
    <button class="botao botao--roxo">Buscar</button>
    <?php if ($filtrando): ?><a class="botao" href="<?= e(url_acervo(['vista' => $vista])) ?>">Limpar</a><?php endif; ?>
  </div>
</form>

<div class="resultado-barra">
  <p class="resultado-total" aria-live="polite">
    <?php if ($vista === 'album'): ?>
      <strong><?= count(cientistas_publicadas()) ?></strong> cientistas, organizadas como álbum de figurinhas
    <?php else: ?>
      <strong><?= $r['total'] ?></strong> <?= $r['total'] === 1 ? 'resultado' : 'resultados' ?>
      <?php if ($f['q'] !== ''): ?> para “<?= e($f['q']) ?>”<?php endif; ?>
      <?php if ($nomeArea): ?> em <?= e($nomeArea) ?><?php endif; ?>
      <?php if ($nomeTema): ?> · tema <?= e($nomeTema) ?><?php endif; ?>
    <?php endif; ?>
  </p>
  <div class="resultado-ferramentas">
    <nav class="abas vidro" aria-label="Modo de exibição">
      <a href="<?= e(url_acervo($f, ['vista' => 'tabela', 'pagina' => 1])) ?>" <?= $vista === 'tabela' ? 'aria-current="page"' : '' ?>>Tabela</a>
      <a href="<?= e(url_acervo($f, ['vista' => 'cartoes', 'pagina' => 1])) ?>" <?= $vista === 'cartoes' ? 'aria-current="page"' : '' ?>>Cartões</a>
      <a href="<?= e(url_acervo([], ['vista' => 'album'])) ?>" <?= $vista === 'album' ? 'aria-current="page"' : '' ?>>Álbum</a>
    </nav>
    <?php if ($vista !== 'album'): ?>
      <?php $exp = url_acervo($f, ['vista' => null, 'pagina' => null], '/exportar.php'); $exp .= (str_contains($exp, '?') ? '&' : '?') . 'formato='; ?>
      <a class="botao" href="<?= e($exp . 'csv') ?>">Exportar CSV</a>
      <a class="botao" href="<?= e($exp . 'json') ?>">JSON</a>
    <?php endif; ?>
  </div>
</div>

<?php if ($vista === 'album'): ?>
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
      <div class="figurinhas"><?php foreach ($lista as $c): ?><?= figurinha($c) ?><?php endforeach; ?></div>
    <?php else: ?>
      <p class="vazio">Esta área ainda não tem cientistas. <a href="/cadastro-cientista.php">Indique uma</a>.</p>
    <?php endif; ?>
  </section>
  <?php endforeach; ?>

<?php elseif (!$r['itens']): ?>
  <div class="vazio vazio--grande">
    <p><strong>Nenhuma cientista encontrada.</strong> Tente outra palavra ou remova algum filtro.</p>
    <p>Conhece alguém que deveria estar aqui? <a href="/cadastro-cientista.php">Indique uma cientista</a>.</p>
  </div>

<?php elseif ($vista === 'tabela'): ?>
  <div class="tabela-rolagem">
    <table class="tabela">
      <thead>
        <tr><th scope="col">Nº</th><th scope="col">Cientista</th><th scope="col">Área</th><th scope="col">Instituição</th><th scope="col">Temas</th></tr>
      </thead>
      <tbody>
        <?php foreach ($r['itens'] as $c): ?>
          <tr class="area-<?= $c['area_id'] ?>">
            <td class="tabela-num"><?= sprintf('%02d', $c['numero']) ?></td>
            <td class="tabela-nome">
              <a href="/cientista.php?id=<?= $c['id'] ?>"><?= e($c['nome']) ?></a>
              <span class="tabela-resumo"><?= e(primeira_frase($c['biografia'], 110)) ?></span>
              <span class="tabela-meta"><?= e($c['area']) ?><?= $c['instituicao'] ? ' · ' . e($c['instituicao']) : '' ?></span>
            </td>
            <td><a class="etiqueta" href="<?= e(url_acervo($f, ['area' => $c['area_id'], 'pagina' => 1])) ?>"><?= e($c['area']) ?></a></td>
            <td class="tabela-inst"><?= e((string) $c['instituicao']) ?></td>
            <td class="tabela-temas">
              <?php foreach ($c['temas'] as $t): ?><a class="chip" href="<?= e(url_acervo($f, ['tema' => $t['id'], 'pagina' => 1])) ?>"><?= e($t['nome']) ?></a><?php endforeach; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php else: ?>
  <div class="cartoes-acervo">
    <?php foreach ($r['itens'] as $c): ?>
      <article class="cartao ficha-acervo area-<?= $c['area_id'] ?>">
        <span class="ficha-arte"><?= simbolo('s-ep-' . $c['variante'], 'ficha-personagem') ?></span>
        <div>
          <p class="miudo">Nº <?= sprintf('%02d', $c['numero']) ?> · <?= e($c['area']) ?></p>
          <h2 class="ficha-nome"><a href="/cientista.php?id=<?= $c['id'] ?>"><?= e($c['nome']) ?></a></h2>
          <?php if ($c['instituicao']): ?><p class="ficha-inst"><?= e($c['instituicao']) ?></p><?php endif; ?>
          <p><?= e(primeira_frase($c['biografia'], 170)) ?></p>
          <p class="ficha-temas"><?php foreach ($c['temas'] as $t): ?><a class="chip" href="<?= e(url_acervo($f, ['tema' => $t['id'], 'pagina' => 1])) ?>"><?= e($t['nome']) ?></a><?php endforeach; ?></p>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if ($vista !== 'album' && $r['itens'] && $paginas > 1): ?>
  <nav class="paginacao" aria-label="Páginas">
    <?php if ($f['pagina'] > 1): ?><a class="botao" href="<?= e(url_acervo($f, ['pagina' => $f['pagina'] - 1])) ?>">← Anterior</a><?php endif; ?>
    <span>Página <?= $f['pagina'] ?> de <?= $paginas ?></span>
    <?php if ($f['pagina'] < $paginas): ?><a class="botao" href="<?= e(url_acervo($f, ['pagina' => $f['pagina'] + 1])) ?>">Próxima →</a><?php endif; ?>
  </nav>
<?php endif; ?>
<?php rodape();
