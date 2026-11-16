<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

$admin = exigir_admin();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$st = db()->prepare('SELECT * FROM cientistas WHERE id = ?');
$st->execute([$id]);
$c = $st->fetch();
if (!$c) {
    nao_encontrado();
}

$areas = areas();
$idsAreas = array_column($areas, 'id');
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_post_com_csrf();
    $c = array_merge($c, [
        'nome'                 => texto_post('nome', 150),
        'area_id'              => (int) ($_POST['area_id'] ?? 0),
        'instituicao'          => texto_post('instituicao', 200),
        'biografia'            => texto_post('biografia', 2000),
        'curiosidade'          => texto_post('curiosidade', 500),
        'fonte'                => texto_post('fonte', 500),
        'autorizacao_registro' => texto_post('autorizacao_registro', 300),
        'destaque'             => ($_POST['destaque'] ?? '') === '1' ? 1 : 0,
    ]);

    if (mb_strlen($c['nome']) < 3) {
        $erros[] = 'Informe o nome da cientista.';
    }
    if (!in_array($c['area_id'], $idsAreas, true)) {
        $erros[] = 'Escolha uma área válida.';
    }
    if (mb_strlen($c['biografia']) < 50) {
        $erros[] = 'A biografia deve ter pelo menos 50 caracteres.';
    }
    if (mb_strlen($c['fonte']) < 5) {
        $erros[] = 'Informe ao menos uma fonte.';
    }
    if ($c['status'] === 'aprovado' && mb_strlen($c['autorizacao_registro']) < 5) {
        $erros[] = 'Uma cientista publicada precisa do registro da autorização.';
    }

    if (!$erros) {
        db()->prepare(
            "UPDATE cientistas SET nome = ?, area_id = ?, instituicao = NULLIF(?, ''), biografia = ?, curiosidade = NULLIF(?, ''),
                    fonte = ?, autorizacao_registro = NULLIF(?, ''), destaque = ?, revisado_por = ?, revisado_em = NOW()
             WHERE id = ?"
        )->execute([
            $c['nome'], $c['area_id'], $c['instituicao'], $c['biografia'], $c['curiosidade'],
            $c['fonte'], $c['autorizacao_registro'], $c['destaque'], $admin['id'], $id,
        ]);
        flash('Alterações salvas.');
        redirecionar('/admin/?status=' . $c['status'] . '#c' . $id);
    }
}

cabecalho('Editar cientista', ['privado' => true]);
?>
<header class="cabeca-pagina">
  <p class="kicker"><a href="/admin/?status=<?= e($c['status']) ?>">← Painel</a></p>
  <h1 class="titulo-pagina">Editar <em>cientista</em></h1>
</header>

<?php lista_erros($erros); ?>
<form class="formulario formulario--largo" method="post" action="/admin/editar.php">
  <?= csrf_campo() ?>
  <input type="hidden" name="id" value="<?= (int) $id ?>">
  <label class="campo">Nome
    <input name="nome" required maxlength="150" value="<?= e($c['nome']) ?>">
  </label>
  <div class="campos-lado">
    <label class="campo">Área
      <select name="area_id" required>
        <?php foreach ($areas as $a): ?>
          <option value="<?= $a['id'] ?>" <?= $a['id'] === (int) $c['area_id'] ? 'selected' : '' ?>><?= e($a['nome']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label class="campo">Instituição
      <input name="instituicao" maxlength="200" value="<?= e((string) $c['instituicao']) ?>">
    </label>
  </div>
  <label class="campo">Biografia
    <textarea name="biografia" required minlength="50" maxlength="2000" rows="6" data-contador><?= e($c['biografia']) ?></textarea>
  </label>
  <label class="campo">Curiosidade
    <textarea name="curiosidade" maxlength="500" rows="3" data-contador><?= e((string) $c['curiosidade']) ?></textarea>
  </label>
  <label class="campo">Fontes
    <input name="fonte" required maxlength="500" value="<?= e($c['fonte']) ?>">
  </label>
  <label class="campo">Registro da autorização (LGPD) <span class="campo-ajuda">como e quando a cientista autorizou; ou “pessoa falecida, dados públicos”</span>
    <input name="autorizacao_registro" maxlength="300" value="<?= e((string) $c['autorizacao_registro']) ?>">
  </label>
  <label class="check"><input type="checkbox" name="destaque" value="1" <?= $c['destaque'] ? 'checked' : '' ?>>
    <span>Marcar como homenageada (selo no álbum e no jogo).</span></label>
  <div class="grupo-botoes">
    <button class="botao botao--roxo botao--grande">Salvar</button>
    <a class="botao botao--grande" href="/admin/?status=<?= e($c['status']) ?>">Cancelar</a>
  </div>
</form>
<?php rodape();
