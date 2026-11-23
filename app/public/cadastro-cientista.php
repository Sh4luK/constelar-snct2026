<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

const MAX_PENDENTES = 10;

$u = exigir_login();
$areas = areas();
$idsAreas = array_column($areas, 'id');
$temas = temas();
$temasMarcados = array_map('intval', (array) ($_POST['temas'] ?? []));
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

    if (mb_strlen($d['nome']) < 3) {
        $erros[] = 'Informe o nome da cientista.';
    }
    if (!in_array($d['area_id'], $idsAreas, true)) {
        $erros[] = 'Escolha uma área válida.';
    }
    if (mb_strlen($d['biografia']) < 50) {
        $erros[] = 'A biografia deve ter pelo menos 50 caracteres.';
    }
    if (mb_strlen($d['fonte']) < 5) {
        $erros[] = 'Informe ao menos uma fonte confiável.';
    }
    if (($_POST['autorizacao'] ?? '') !== '1') {
        $erros[] = 'É necessário declarar a autorização de uso dos dados (LGPD).';
    }
    if (!$erros) {
        $st = db()->prepare("SELECT COUNT(*) FROM cientistas WHERE enviado_por = ? AND status = 'pendente'");
        $st->execute([$u['id']]);
        if ((int) $st->fetchColumn() >= MAX_PENDENTES) {
            $erros[] = 'Você já tem ' . MAX_PENDENTES . ' indicações aguardando revisão. Espere a administração avaliar.';
        }
    }

    if (!$erros) {
        db()->prepare(
            "INSERT INTO cientistas (nome, area_id, instituicao, biografia, curiosidade, fonte, autorizacao_uso, status, enviado_por)
             VALUES (?, ?, NULLIF(?, ''), ?, NULLIF(?, ''), ?, 1, 'pendente', ?)"
        )->execute([$d['nome'], $d['area_id'], $d['instituicao'], $d['biografia'], $d['curiosidade'], $d['fonte'], $u['id']]);
        $idsValidos = array_column($temas, 'id');
        salvar_temas((int) db()->lastInsertId(), array_intersect($temasMarcados, $idsValidos), '');
        flash('Indicação enviada! Ela aparece no álbum e no jogo depois da revisão da administração.');
        redirecionar('/conta.php');
    }
}

cabecalho('Indicar cientista', ['privado' => true]);
?>
<header class="cabeca-pagina">
  <p class="kicker">Nova estrela</p>
  <h1 class="titulo-pagina">Indicar <em>cientista</em></h1>
  <p class="lede">Conte quem ela é e o que pesquisa. A administração revisa cada indicação antes de publicar.</p>
</header>

<div class="form-duas">
  <div>
    <?php lista_erros($erros); ?>
    <form class="formulario" method="post" action="/cadastro-cientista.php">
      <?= csrf_campo() ?>
      <label class="campo">Nome da cientista
        <input name="nome" required maxlength="150" value="<?= e($d['nome']) ?>">
      </label>
      <label class="campo">Área do conhecimento
        <select name="area_id" required>
          <option value="">Selecione…</option>
          <?php foreach ($areas as $a): ?>
            <option value="<?= $a['id'] ?>" <?= $a['id'] === $d['area_id'] ? 'selected' : '' ?>><?= e($a['nome']) ?> (<?= e($a['constelacao']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="campo">Instituição <span class="campo-ajuda">opcional</span>
        <input name="instituicao" maxlength="200" value="<?= e($d['instituicao']) ?>">
      </label>
      <label class="campo">Biografia <span class="campo-ajuda">é o que a estrela conta no jogo</span>
        <textarea name="biografia" required minlength="50" maxlength="2000" rows="6" data-contador><?= e($d['biografia']) ?></textarea>
      </label>
      <label class="campo">Curiosidade <span class="campo-ajuda">opcional; uma frase marcante</span>
        <textarea name="curiosidade" maxlength="500" rows="3" data-contador><?= e($d['curiosidade']) ?></textarea>
      </label>
      <fieldset class="campo campo-temas">
        <legend>Temas <span class="campo-ajuda">opcional; ajudam na busca</span></legend>
        <div class="temas-marcar">
          <?php foreach ($temas as $t): ?>
            <label class="chip-check"><input type="checkbox" name="temas[]" value="<?= $t['id'] ?>" <?= in_array($t['id'], $temasMarcados, true) ? 'checked' : '' ?>><span><?= e($t['nome']) ?></span></label>
          <?php endforeach; ?>
        </div>
      </fieldset>
      <label class="campo">Fontes <span class="campo-ajuda">sites, artigos, entrevistas</span>
        <input name="fonte" required maxlength="500" value="<?= e($d['fonte']) ?>">
      </label>
      <label class="check"><input type="checkbox" name="autorizacao" value="1" required>
        <span>Declaro que a cientista autorizou o uso destas informações neste projeto, ou que se trata de pessoa falecida com dados públicos (LGPD, Lei nº 13.709/2018).</span></label>
      <button class="botao botao--roxo botao--grande">Enviar para revisão</button>
    </form>
  </div>

  <aside class="dicas">
    <?= simbolo('s-lume', 'dicas-lume') ?>
    <h2>Dicas da Lume</h2>
    <ul>
      <li><strong>Escreva simples.</strong> Quem vai ler são estudantes: troque termos difíceis por explicações curtas.</li>
      <li><strong>Frases curtas.</strong> No jogo, a biografia aparece aos poucos, uma ou duas frases por vez.</li>
      <li><strong>Sem o nome no texto.</strong> A estrela já apresenta a cientista; a biografia conta o que ela faz.</li>
      <li><strong>Cite as fontes.</strong> Site da universidade, entrevistas e reportagens confiáveis.</li>
      <li><strong>Peça autorização.</strong> Só publicamos cientistas que concordaram em aparecer aqui.</li>
    </ul>
  </aside>
</div>
<?php rodape();
