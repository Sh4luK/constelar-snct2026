<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$lista = db()->query("SELECT c.nome, c.instituicao, c.biografia, a.nome AS area, a.cor
                      FROM cientistas c JOIN areas a ON a.id = c.area_id
                      WHERE c.status = 'aprovado' AND c.autorizacao_uso = 1
                      ORDER BY a.nome, c.nome")->fetchAll();

cabecalho('Cientistas');
?>
<h1>Cientistas no Constelar</h1>
<div class="grade">
<?php foreach ($lista as $c): ?>
  <article class="cartao cientista">
    <p class="etiqueta"><svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path fill="<?= e($c['cor']) ?>" d="M12 2l2.9 6.9L22 10l-5.5 4.7L18.2 22 12 18.3 5.8 22l1.7-7.3L2 10l7.1-1.1z"/></svg> <?= e($c['area']) ?></p>
    <h2><?= e($c['nome']) ?></h2>
    <?php if ($c['instituicao']): ?><p class="inst"><?= e($c['instituicao']) ?></p><?php endif; ?>
    <p><?= e($c['biografia']) ?></p>
  </article>
<?php endforeach; ?>
</div>
<?php rodape();
