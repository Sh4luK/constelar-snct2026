<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$u = exigir_login();
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_post_com_csrf();
    $atual = senha_post('senha_atual');
    $nova = senha_post('senha_nova');

    $st = db()->prepare('SELECT senha_hash FROM usuarios WHERE id = ?');
    $st->execute([$u['id']]);
    $hash = (string) $st->fetchColumn();

    if (limite_excedido('senha', 8, 15)) {
        $erros[] = 'Muitas tentativas. Aguarde alguns minutos.';
    } elseif (!password_verify($atual, $hash)) {
        registrar_tentativa('senha');
        $erros[] = 'A senha atual não confere.';
    } elseif (strlen($nova) < 8 || strlen($nova) > 72) {
        $erros[] = 'A nova senha deve ter entre 8 e 72 caracteres.';
    } else {
        db()->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?')
            ->execute([password_hash($nova, PASSWORD_DEFAULT), $u['id']]);
        session_regenerate_id(true);
        flash('Senha alterada.');
        redirecionar('/conta.php');
    }
}

$st = db()->prepare(
    'SELECT c.id, c.nome, c.status, c.nota_revisao, c.criado_em, a.nome AS area
     FROM cientistas c JOIN areas a ON a.id = c.area_id
     WHERE c.enviado_por = ? ORDER BY c.criado_em DESC'
);
$st->execute([$u['id']]);
$indicacoes = $st->fetchAll();
$rotulos = ['pendente' => 'Em revisão', 'aprovado' => 'Publicada', 'rejeitado' => 'Não publicada'];

cabecalho('Minha conta', ['privado' => true]);
?>
<header class="cabeca-pagina">
  <p class="kicker">Minha conta</p>
  <h1 class="titulo-pagina">Olá, <em><?= e($u['nome']) ?></em></h1>
</header>

<section class="secao secao--colada">
  <div class="secao-cabeca">
    <h2 class="titulo-secao titulo-secao--menor">Minhas indicações</h2>
    <a class="botao botao--roxo" href="/cadastro-cientista.php">Indicar cientista</a>
  </div>
  <?php if (!$indicacoes): ?>
    <p class="vazio">Você ainda não indicou nenhuma cientista.</p>
  <?php else: ?>
    <ul class="lista-indicacoes">
      <?php foreach ($indicacoes as $i): ?>
        <li class="cartao indicacao">
          <div>
            <strong class="indicacao-nome"><?= e($i['nome']) ?></strong>
            <span class="miudo"><?= e($i['area']) ?> · enviada em <?= e(date('d/m/Y', strtotime($i['criado_em']))) ?></span>
            <?php if ($i['status'] === 'rejeitado' && $i['nota_revisao']): ?>
              <p class="indicacao-nota"><strong>Recado da revisão:</strong> <?= e($i['nota_revisao']) ?></p>
            <?php endif; ?>
          </div>
          <?php if ($i['status'] === 'aprovado'): ?>
            <a class="status status--aprovado" href="/cientista.php?id=<?= (int) $i['id'] ?>"><?= e($rotulos[$i['status']]) ?></a>
          <?php else: ?>
            <span class="status status--<?= e($i['status']) ?>"><?= e($rotulos[$i['status']]) ?></span>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</section>

<section class="secao">
  <h2 class="titulo-secao titulo-secao--menor">Trocar a senha</h2>
  <?php lista_erros($erros); ?>
  <form class="formulario formulario--estreito" method="post" action="/conta.php">
    <?= csrf_campo() ?>
    <label class="campo">Senha atual
      <input type="password" name="senha_atual" required autocomplete="current-password">
    </label>
    <label class="campo">Nova senha <span class="campo-ajuda">8 caracteres ou mais</span>
      <input type="password" name="senha_nova" required minlength="8" maxlength="72" autocomplete="new-password">
    </label>
    <button class="botao botao--tinta">Salvar nova senha</button>
  </form>
</section>
<?php rodape();
