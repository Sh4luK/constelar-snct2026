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

$temas = temas();
$temasMarcados = array_column(temas_por_cientista([$id])[$id] ?? [], 'id');
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
        'foto_credito'         => texto_post('foto_credito', 300),
        'foto_fonte'           => texto_post('foto_fonte', 300),
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

    $fotoAntiga = $c['foto'];
    if (!$erros) {
        try {
            if ($nova = receber_foto('foto')) {
                $c['foto'] = $nova;
            } elseif (($_POST['remover_foto'] ?? '') === '1') {
                $c['foto'] = null;
            }
        } catch (InvalidArgumentException $ex) {
            $erros[] = $ex->getMessage();
        }
        if (!$erros && $c['foto'] && mb_strlen($c['foto_credito']) < 3) {
            $erros[] = 'Informe o crédito da foto.';
        }
        if ($erros && $c['foto'] !== $fotoAntiga) {
            apagar_foto($c['foto']);
            $c['foto'] = $fotoAntiga;
        }
    }
    if ($c['foto_fonte'] !== '' && !preg_match('#^https://#', $c['foto_fonte'])) {
        $erros[] = 'O link da fonte da foto deve começar com https://';
    }

    if (!$erros) {
        db()->prepare(
            "UPDATE cientistas SET nome = ?, area_id = ?, instituicao = NULLIF(?, ''), biografia = ?, curiosidade = NULLIF(?, ''),
                    fonte = ?, autorizacao_registro = NULLIF(?, ''), destaque = ?, foto = ?, foto_credito = NULLIF(?, ''),
                    foto_fonte = NULLIF(?, ''), revisado_por = ?, revisado_em = NOW()
             WHERE id = ?"
        )->execute([
            $c['nome'], $c['area_id'], $c['instituicao'], $c['biografia'], $c['curiosidade'],
            $c['fonte'], $c['autorizacao_registro'], $c['destaque'], $c['foto'], $c['foto'] ? $c['foto_credito'] : '',
            $c['foto'] ? $c['foto_fonte'] : '', $admin['id'], $id,
        ]);
        if ($fotoAntiga && $fotoAntiga !== $c['foto']) {
            apagar_foto($fotoAntiga);
        }
        $temasMarcados = array_map('intval', (array) ($_POST['temas'] ?? []));
        salvar_temas($id, array_intersect($temasMarcados, array_column($temas, 'id')), texto_post('temas_novos', 300));
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
<form class="formulario formulario--largo" method="post" action="/admin/editar.php" enctype="multipart/form-data">
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
  <fieldset class="campo campo-temas">
    <legend>Temas</legend>
    <div class="temas-marcar">
      <?php foreach ($temas as $t): ?>
        <label class="chip-check"><input type="checkbox" name="temas[]" value="<?= $t['id'] ?>" <?= in_array($t['id'], $temasMarcados, true) ? 'checked' : '' ?>><span><?= e($t['nome']) ?></span></label>
      <?php endforeach; ?>
    </div>
    <input name="temas_novos" maxlength="300" placeholder="Novos temas, separados por vírgula" aria-label="Novos temas">
  </fieldset>
  <label class="campo">Fontes
    <input name="fonte" required maxlength="500" value="<?= e($c['fonte']) ?>">
  </label>
  <fieldset class="campo campo-foto">
    <legend>Foto</legend>
    <div class="foto-envio">
      <span class="foto-previa area-<?= (int) $c['area_id'] ?>" data-previa><?= $c['foto'] ? retrato($c) : simbolo('s-ep-' . variante_personagem((int) $c['id']), 'retrato-personagem') ?></span>
      <div class="foto-envio-campos">
        <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" data-foto aria-label="Enviar nova foto">
        <?php if ($c['foto']): ?><label class="check"><input type="checkbox" name="remover_foto" value="1"><span>Remover a foto (volta a personagem ilustrada)</span></label><?php endif; ?>
        <label class="campo">Crédito da foto
          <input name="foto_credito" maxlength="300" value="<?= e((string) $c['foto_credito']) ?>" placeholder="Autor · licença · onde foi publicada">
        </label>
        <label class="campo">Link da fonte da foto <span class="campo-ajuda">opcional</span>
          <input type="url" name="foto_fonte" maxlength="300" value="<?= e((string) $c['foto_fonte']) ?>" placeholder="https://">
        </label>
      </div>
    </div>
  </fieldset>
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
