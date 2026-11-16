<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

exigir_admin();
$pdo = db();
$erros = [];

/** Lê e valida os campos de uma constelação. */
function ler_area(array &$erros): array
{
    $a = [
        'nome'        => texto_post('nome', 80),
        'constelacao' => texto_post('constelacao', 80),
        'descricao'   => texto_post('descricao', 500),
        'cor'         => strtolower(texto_post('cor', 7)),
        'icone'       => texto_post('icone', 20),
        'ordem'       => max(0, min(999, (int) ($_POST['ordem'] ?? 0))),
    ];
    if (mb_strlen($a['nome']) < 2) {
        $erros[] = 'Informe o nome da área.';
    }
    if (mb_strlen($a['constelacao']) < 2) {
        $erros[] = 'Informe o nome da constelação.';
    }
    if (mb_strlen($a['descricao']) < 5) {
        $erros[] = 'Escreva uma descrição curta.';
    }
    if (!preg_match('/^#[0-9a-f]{6}$/', $a['cor'])) {
        $erros[] = 'Cor inválida.';
    }
    if (!isset(ICONES_AREA[$a['icone']])) {
        $erros[] = 'Símbolo inválido.';
    }
    return $a;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_post_com_csrf();
    $acao = is_string($_POST['acao'] ?? null) ? $_POST['acao'] : '';
    $id = (int) ($_POST['id'] ?? 0);

    try {
        if ($acao === 'excluir') {
            $st = $pdo->prepare('SELECT COUNT(*) FROM cientistas WHERE area_id = ?');
            $st->execute([$id]);
            if ((int) $st->fetchColumn() > 0) {
                $erros[] = 'Só é possível excluir uma constelação sem cientistas (em qualquer situação).';
            } else {
                $pdo->prepare('DELETE FROM areas WHERE id = ?')->execute([$id]);
                flash('Constelação excluída.');
                redirecionar('/admin/areas.php');
            }
        } elseif ($acao === 'salvar' || $acao === 'criar') {
            $a = ler_area($erros);
            if (!$erros) {
                if ($acao === 'criar') {
                    $pdo->prepare('INSERT INTO areas (nome, constelacao, descricao, cor, icone, ordem) VALUES (?, ?, ?, ?, ?, ?)')
                        ->execute([$a['nome'], $a['constelacao'], $a['descricao'], $a['cor'], $a['icone'], $a['ordem']]);
                    flash('Constelação criada.');
                } else {
                    $pdo->prepare('UPDATE areas SET nome = ?, constelacao = ?, descricao = ?, cor = ?, icone = ?, ordem = ? WHERE id = ?')
                        ->execute([$a['nome'], $a['constelacao'], $a['descricao'], $a['cor'], $a['icone'], $a['ordem'], $id]);
                    flash('Constelação atualizada.');
                }
                redirecionar('/admin/areas.php');
            }
        } else {
            http_response_code(400);
            exit('Ação inválida.');
        }
    } catch (PDOException $ex) {
        if (($ex->errorInfo[1] ?? 0) !== 1062) {
            throw $ex;
        }
        $erros[] = 'Já existe uma área com esse nome.';
    }
}

$lista = $pdo->query(
    'SELECT a.*, (SELECT COUNT(*) FROM cientistas c WHERE c.area_id = a.id) AS cientistas FROM areas a ORDER BY a.ordem, a.id'
)->fetchAll();

/** Campos de formulário de uma constelação. */
function campos_area(array $a): void
{
    ?>
    <div class="campos-lado">
      <label class="campo">Área do conhecimento
        <input name="nome" required maxlength="80" value="<?= e($a['nome'] ?? '') ?>">
      </label>
      <label class="campo">Nome da constelação
        <input name="constelacao" required maxlength="80" value="<?= e($a['constelacao'] ?? '') ?>" placeholder="Constelação do…">
      </label>
    </div>
    <label class="campo">Descrição curta
      <input name="descricao" required maxlength="500" value="<?= e($a['descricao'] ?? '') ?>">
    </label>
    <div class="campos-lado campos-lado--tres">
      <label class="campo">Símbolo
        <select name="icone">
          <?php foreach (ICONES_AREA as $k => $rotulo): ?>
            <option value="<?= e($k) ?>" <?= ($a['icone'] ?? 'estrela') === $k ? 'selected' : '' ?>><?= e($rotulo) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="campo">Cor
        <input type="color" name="cor" value="<?= e($a['cor'] ?? '#b69cff') ?>">
      </label>
      <label class="campo">Ordem
        <input type="number" name="ordem" min="0" max="999" value="<?= (int) ($a['ordem'] ?? count(areas()) + 1) ?>">
      </label>
    </div>
    <?php
}

cabecalho('Constelações', ['privado' => true]);
?>
<header class="cabeca-pagina">
  <p class="kicker"><a href="/admin/">← Painel</a></p>
  <h1 class="titulo-pagina">Constelações <em>e áreas</em></h1>
  <p class="lede">Cada área do conhecimento é uma constelação no jogo. O símbolo define o desenho formado pelas estrelas.</p>
</header>

<?php lista_erros($erros); ?>

<div class="revisoes">
<?php foreach ($lista as $a): ?>
  <article class="cartao revisao area-<?= (int) $a['id'] ?>">
    <header class="revisao-cabeca">
      <?= simbolo('i-' . $a['icone'], 'area-ic') ?>
      <h2 class="revisao-nome"><?= e($a['constelacao']) ?></h2>
      <span class="miudo"><?= (int) $a['cientistas'] ?> <?= (int) $a['cientistas'] === 1 ? 'cientista' : 'cientistas' ?></span>
    </header>
    <form method="post" action="/admin/areas.php" class="formulario formulario--solto">
      <?= csrf_campo() ?>
      <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
      <?php campos_area($a); ?>
      <div class="grupo-botoes">
        <button class="botao botao--tinta" name="acao" value="salvar">Salvar</button>
        <?php if ((int) $a['cientistas'] === 0): ?>
          <button class="botao botao--perigo" name="acao" value="excluir" formnovalidate data-confirmar="Excluir a <?= e($a['constelacao']) ?>?">Excluir</button>
        <?php endif; ?>
      </div>
    </form>
  </article>
<?php endforeach; ?>

  <article class="cartao revisao revisao--nova">
    <h2 class="revisao-nome">Nova constelação</h2>
    <form method="post" action="/admin/areas.php" class="formulario formulario--solto">
      <?= csrf_campo() ?>
      <?php campos_area([]); ?>
      <button class="botao botao--roxo" name="acao" value="criar">Criar constelação</button>
    </form>
  </article>
</div>
<?php rodape();
