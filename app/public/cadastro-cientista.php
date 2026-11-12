<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$u = exigir_login();
$areas = db()->query('SELECT id, nome FROM areas ORDER BY nome')->fetchAll();
$idsAreas = array_map('intval', array_column($areas, 'id'));
$erros = [];
$d = ['nome' => '', 'area_id' => 0, 'instituicao' => '', 'biografia' => '', 'curiosidade' => '', 'fonte' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_post_com_csrf();
    $d = [
        'nome'        => texto_post('nome', 150),
        'area_id'     => (int) ($_POST['area_id'] ?? 0),
        'instituicao' => texto_post('instituicao', 200),
        'biografia'   => texto_post('biografia', 2000),
        'curiosidade' => texto_post('curiosidade', 500),
        'fonte'       => texto_post('fonte', 500),
    ];
    $autorizacao = ($_POST['autorizacao'] ?? '') === '1';

    if (mb_strlen($d['nome']) < 3)        $erros[] = 'Informe o nome da cientista.';
    if (!in_array($d['area_id'], $idsAreas, true)) $erros[] = 'Escolha uma área válida.';
    if (mb_strlen($d['biografia']) < 50)  $erros[] = 'A biografia deve ter pelo menos 50 caracteres.';
    if (mb_strlen($d['fonte']) < 5)       $erros[] = 'Informe ao menos uma fonte confiável.';
    if (!$autorizacao)                    $erros[] = 'É necessário declarar a autorização de uso dos dados (LGPD).';

    if (!$erros) {
        db()->prepare("INSERT INTO cientistas (nome, area_id, instituicao, biografia, curiosidade, fonte, autorizacao_uso, status, enviado_por)
                       VALUES (?, ?, NULLIF(?, ''), ?, NULLIF(?, ''), ?, 1, 'pendente', ?)")
            ->execute([$d['nome'], $d['area_id'], $d['instituicao'], $d['biografia'], $d['curiosidade'], $d['fonte'], $u['id']]);
        flash('Indicação enviada! Ela aparecerá no jogo depois da aprovação da administração.');
        redirecionar('/cadastro-cientista.php');
    }
}

cabecalho('Indicar cientista');
?>
<section class="cartao">
  <h1>Indicar cientista</h1>
  <?php foreach ($erros as $er): ?><p class="erro"><?= e($er) ?></p><?php endforeach; ?>
  <form method="post" action="/cadastro-cientista.php">
    <?= csrf_campo() ?>
    <label>Nome da cientista <input name="nome" required maxlength="150" value="<?= e($d['nome']) ?>"></label>
    <label>Área do conhecimento
      <select name="area_id" required>
        <option value="">Selecione…</option>
        <?php foreach ($areas as $a): ?>
          <option value="<?= (int) $a['id'] ?>" <?= (int) $a['id'] === $d['area_id'] ? 'selected' : '' ?>><?= e($a['nome']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Instituição <input name="instituicao" maxlength="200" value="<?= e($d['instituicao']) ?>"></label>
    <label>Biografia (aparece no diálogo do jogo) <textarea name="biografia" required minlength="50" maxlength="2000" rows="6"><?= e($d['biografia']) ?></textarea></label>
    <label>Curiosidade <textarea name="curiosidade" maxlength="500" rows="3"><?= e($d['curiosidade']) ?></textarea></label>
    <label>Fontes (sites, artigos, entrevistas) <input name="fonte" required maxlength="500" value="<?= e($d['fonte']) ?>"></label>
    <label class="check"><input type="checkbox" name="autorizacao" value="1" required>
      Declaro que a cientista autorizou o uso destas informações neste projeto, ou que se trata de pessoa falecida com dados públicos (LGPD, Lei nº 13.709/2018).</label>
    <button class="botao">Enviar para revisão</button>
  </form>
</section>
<?php rodape();
