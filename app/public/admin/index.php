<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

exigir_admin();

$estados = ['pendente' => 'Pendentes', 'aprovado' => 'Publicadas', 'rejeitado' => 'Rejeitadas'];
$filtro = isset($estados[$_GET['status'] ?? '']) ? $_GET['status'] : 'pendente';

$busca = is_string($_GET['q'] ?? null) ? trim(mb_substr($_GET['q'], 0, 80)) : '';
$st = db()->prepare(
    'SELECT c.*, a.nome AS area, a.constelacao, u.nome AS autor, u.email AS autor_email
     FROM cientistas c
     JOIN areas a ON a.id = c.area_id
     LEFT JOIN usuarios u ON u.id = c.enviado_por
     WHERE c.status = ? AND (? = \'\' OR c.nome LIKE ? OR u.email LIKE ?)
     ORDER BY c.criado_em DESC, c.id DESC'
);
$like = '%' . addcslashes($busca, '%_\\') . '%';
$st->execute([$filtro, $busca, $like, $like]);
$lista = $st->fetchAll();

$contagem = db()->query('SELECT status, COUNT(*) FROM cientistas GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
$aConfirmar = (int) db()->query(
    "SELECT COUNT(*) FROM cientistas WHERE status = 'aprovado' AND (autorizacao_registro IS NULL OR autorizacao_registro LIKE 'Demonstração%')"
)->fetchColumn();

cabecalho('Painel de revisão', ['privado' => true]);
?>
<header class="cabeca-pagina">
  <p class="kicker">Administração</p>
  <h1 class="titulo-pagina">Painel de <em>revisão</em></h1>
</header>

<div class="painel-barra">
  <nav class="abas vidro" aria-label="Situação">
    <?php foreach ($estados as $k => $rotulo): ?>
      <a href="?status=<?= $k ?>" <?= $k === $filtro ? 'aria-current="page"' : '' ?>><?= $rotulo ?> <span><?= (int) ($contagem[$k] ?? 0) ?></span></a>
    <?php endforeach; ?>
  </nav>
  <form class="busca-admin" method="get" action="/admin/">
    <input type="hidden" name="status" value="<?= e($filtro) ?>">
    <input type="search" name="q" value="<?= e($busca) ?>" placeholder="Buscar por nome ou e-mail" aria-label="Buscar no painel">
  </form>
  <a class="botao" href="/admin/areas.php">Constelações</a>
  <a class="botao" href="/dados.php">Estatísticas</a>
</div>

<?php if ($aConfirmar && $filtro === 'aprovado'): ?>
  <p class="aviso aviso--alerta"><strong><?= $aConfirmar ?> <?= $aConfirmar === 1 ? 'cientista publicada está' : 'cientistas publicadas estão' ?> sem autorização confirmada.</strong> São dados de demonstração: registre a autorização de cada uma (em “Editar”) antes do uso público.</p>
<?php endif; ?>

<?php if (!$lista): ?><p class="vazio">Nenhuma cientista nesta lista.</p><?php endif; ?>

<div class="revisoes">
<?php foreach ($lista as $c):
    $semAutorizacao = $c['status'] === 'aprovado'
        && ($c['autorizacao_registro'] === null || str_starts_with($c['autorizacao_registro'], 'Demonstração'));
?>
  <article class="cartao revisao area-<?= (int) $c['area_id'] ?>" id="c<?= (int) $c['id'] ?>">
    <header class="revisao-cabeca">
      <span class="etiqueta"><?= e($c['area']) ?></span>
      <span class="miudo">enviada em <?= e(date('d/m/Y H:i', strtotime($c['criado_em']))) ?><?= $c['autor'] ? ' por ' . e($c['autor']) . ' (' . e($c['autor_email']) . ')' : ' (acervo inicial)' ?></span>
      <?php if ($semAutorizacao): ?><span class="status status--pendente">Autorização a confirmar</span><?php endif; ?>
    </header>
    <div class="revisao-pessoa"><?= retrato($c, 'revisao-retrato') ?><h2 class="revisao-nome"><?= e($c['nome']) ?></h2></div>
    <?php if ($c['instituicao']): ?><p class="perfil-inst"><?= e($c['instituicao']) ?></p><?php endif; ?>
    <p><?= e($c['biografia']) ?></p>
    <dl class="revisao-dados">
      <?php if ($c['curiosidade']): ?><dt>Curiosidade</dt><dd><?= e($c['curiosidade']) ?></dd><?php endif; ?>
      <dt>Fontes</dt><dd><?= e($c['fonte']) ?></dd>
      <?php if ($c['foto']): ?><dt>Crédito da foto</dt><dd><?= e((string) $c['foto_credito']) ?></dd><?php endif; ?>
      <dt>Autorização (LGPD)</dt>
      <dd><?= $c['autorizacao_registro'] ? e($c['autorizacao_registro']) : ($c['autorizacao_uso'] ? 'Declarada por quem indicou; ainda sem registro da administração.' : 'Não declarada.') ?></dd>
      <?php if ($c['nota_revisao']): ?><dt>Recado da revisão</dt><dd><?= e($c['nota_revisao']) ?></dd><?php endif; ?>
    </dl>

    <div class="acoes">
      <?php if ($filtro !== 'aprovado'): ?>
        <details class="acao-detalhe">
          <summary class="botao botao--roxo">Aprovar…</summary>
          <form method="post" action="/admin/acao.php" class="acao-form">
            <?= csrf_campo() ?>
            <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
            <input type="hidden" name="volta" value="<?= e($filtro) ?>">
            <label class="campo">Como a autorização foi obtida?
              <input name="registro" required minlength="5" maxlength="300" value="<?= e((string) $c['autorizacao_registro']) ?>" placeholder="Ex.: e-mail da cientista em 10/11/2026">
            </label>
            <button class="botao botao--roxo" name="acao" value="aprovar">Confirmar e publicar</button>
          </form>
        </details>
      <?php endif; ?>
      <?php if ($filtro === 'pendente'): ?>
        <details class="acao-detalhe">
          <summary class="botao">Rejeitar…</summary>
          <form method="post" action="/admin/acao.php" class="acao-form">
            <?= csrf_campo() ?>
            <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
            <input type="hidden" name="volta" value="<?= e($filtro) ?>">
            <label class="campo">Recado para quem indicou <span class="campo-ajuda">opcional</span>
              <input name="nota" maxlength="300" placeholder="Ex.: faltou citar as fontes">
            </label>
            <button class="botao botao--tinta" name="acao" value="rejeitar">Confirmar rejeição</button>
          </form>
        </details>
      <?php endif; ?>
      <a class="botao" href="/admin/editar.php?id=<?= (int) $c['id'] ?>">Editar</a>
      <form method="post" action="/admin/acao.php" class="acao-simples">
        <?= csrf_campo() ?>
        <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
        <input type="hidden" name="volta" value="<?= e($filtro) ?>">
        <?php if ($filtro !== 'pendente'): ?>
          <button class="botao" name="acao" value="pendente"><?= $filtro === 'aprovado' ? 'Despublicar' : 'Voltar para revisão' ?></button>
        <?php endif; ?>
        <button class="botao botao--perigo" name="acao" value="excluir" data-confirmar="Excluir “<?= e($c['nome']) ?>” definitivamente?">Excluir</button>
      </form>
    </div>
  </article>
<?php endforeach; ?>
</div>
<?php rodape();
