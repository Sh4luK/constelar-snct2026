<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

exigir_admin();

$filtro = in_array($_GET['status'] ?? '', ['pendente', 'aprovado', 'rejeitado'], true) ? $_GET['status'] : 'pendente';
$st = db()->prepare("SELECT c.*, a.nome AS area, u.nome AS autor, u.email AS autor_email
                     FROM cientistas c
                     JOIN areas a ON a.id = c.area_id
                     LEFT JOIN usuarios u ON u.id = c.enviado_por
                     WHERE c.status = ?
                     ORDER BY c.criado_em DESC");
$st->execute([$filtro]);
$lista = $st->fetchAll();

$contagem = db()->query('SELECT status, COUNT(*) n FROM cientistas GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);

cabecalho('Painel do administrador');
?>
<header class="cabecalho-pagina">
  <p class="kicker">Administração</p>
  <h1 class="titulo-secao">Painel de <em>revisão</em></h1>
</header>
<nav class="abas glass">
  <?php foreach (['pendente' => 'Pendentes', 'aprovado' => 'Aprovadas', 'rejeitado' => 'Rejeitadas'] as $k => $rotulo): ?>
    <a href="?status=<?= $k ?>" class="<?= $k === $filtro ? 'ativa' : '' ?>"><?= $rotulo ?> (<?= (int) ($contagem[$k] ?? 0) ?>)</a>
  <?php endforeach; ?>
</nav>

<?php if (!$lista): ?><p>Nenhuma cientista nesta lista.</p><?php endif; ?>

<?php foreach ($lista as $c): ?>
<article class="cartao">
  <p class="etiqueta"><?= e($c['area']) ?> · enviado em <?= e($c['criado_em']) ?><?= $c['autor'] ? ' por ' . e($c['autor']) . ' &lt;' . e($c['autor_email']) . '&gt;' : '' ?></p>
  <h2><?= e($c['nome']) ?></h2>
  <?php if ($c['instituicao']): ?><p class="inst"><?= e($c['instituicao']) ?></p><?php endif; ?>
  <p><?= e($c['biografia']) ?></p>
  <?php if ($c['curiosidade']): ?><p><strong>Curiosidade:</strong> <?= e($c['curiosidade']) ?></p><?php endif; ?>
  <p><strong>Fontes:</strong> <?= e($c['fonte']) ?></p>
  <p><strong>Autorização LGPD declarada:</strong> <?= $c['autorizacao_uso'] ? 'sim' : 'não' ?></p>
  <form method="post" action="/admin/acao.php" class="acoes">
    <?= csrf_campo() ?>
    <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
    <input type="hidden" name="volta" value="<?= e($filtro) ?>">
    <?php if ($filtro !== 'aprovado'): ?><button class="botao glass glass--azul glass--interativo" name="acao" value="aprovar">Aprovar</button><?php endif; ?>
    <?php if ($filtro !== 'rejeitado'): ?><button class="botao glass glass--interativo" name="acao" value="rejeitar">Rejeitar</button><?php endif; ?>
    <button class="botao botao--perigo glass glass--interativo" name="acao" value="excluir" data-confirmar="Excluir definitivamente?">Excluir</button>
  </form>
</article>
<?php endforeach; ?>
<script src="/assets/js/confirmar.js"></script>
<?php rodape();
