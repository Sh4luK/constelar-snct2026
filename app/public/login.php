<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

const MAX_TENTATIVAS = 8;
const JANELA_MIN = 15;

$erro = null;
$voltar = is_string($_GET['voltar'] ?? null) ? $_GET['voltar'] : '/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_post_com_csrf();
    $voltar = is_string($_POST['voltar'] ?? null) ? $_POST['voltar'] : '/';
    $ip = inet_pton($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0') ?: "\0\0\0\0";
    $pdo = db();
    $pdo->prepare('DELETE FROM tentativas_login WHERE criado_em < NOW() - INTERVAL 1 DAY')->execute();
    $st = $pdo->prepare('SELECT COUNT(*) FROM tentativas_login WHERE ip = ? AND criado_em > NOW() - INTERVAL ' . JANELA_MIN . ' MINUTE');
    $st->execute([$ip]);

    if ((int) $st->fetchColumn() >= MAX_TENTATIVAS) {
        http_response_code(429);
        $erro = 'Muitas tentativas. Aguarde alguns minutos.';
    } else {
        $email = texto_post('email', 190);
        $senha = is_string($_POST['senha'] ?? null) ? $_POST['senha'] : '';
        $st = $pdo->prepare('SELECT id, nome, papel, senha_hash FROM usuarios WHERE email = ?');
        $st->execute([$email]);
        $u = $st->fetch();
        // Usuário inexistente: gasta o mesmo custo de hash para não revelar quais e-mails existem.
        $ok = $u ? password_verify($senha, $u['senha_hash']) : (password_hash($senha, PASSWORD_DEFAULT) && false);
        if ($ok) {
            if (password_needs_rehash($u['senha_hash'], PASSWORD_DEFAULT)) {
                $pdo->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?')->execute([password_hash($senha, PASSWORD_DEFAULT), $u['id']]);
            }
            autenticar($u);
            redirecionar($voltar);
        }
        $pdo->prepare('INSERT INTO tentativas_login (ip) VALUES (?)')->execute([$ip]);
        $erro = 'E-mail ou senha incorretos.';
    }
}

cabecalho('Entrar');
?>
<section class="estreito cabecalho-pagina">
  <p class="kicker">Bem-vinda de volta</p>
  <h1 class="titulo-secao">Entrar</h1>
  <?php if ($erro): ?><p class="erro"><?= e($erro) ?></p><?php endif; ?>
  <form method="post" action="/login.php">
    <?= csrf_campo() ?>
    <input type="hidden" name="voltar" value="<?= e($voltar) ?>">
    <label>E-mail <input type="email" name="email" required maxlength="190" autocomplete="username"></label>
    <label>Senha <input type="password" name="senha" required autocomplete="current-password"></label>
    <button class="botao glass glass--azul glass--interativo">Entrar</button>
  </form>
  <p>Não tem conta? <a href="/registrar.php">Cadastre-se</a>.</p>
</section>
<?php rodape();
