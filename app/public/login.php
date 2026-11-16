<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

// O formulário precisa do cookie de sessão antes de qualquer saída (o token CSRF mora nela).
csrf_token();

const LOGIN_MAX = 8;
const LOGIN_JANELA_MIN = 15;

if (usuario_atual()) {
    redirecionar('/conta.php');
}

$erros = [];
$voltar = is_string($_GET['voltar'] ?? null) ? $_GET['voltar'] : '/conta.php';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_post_com_csrf();
    $voltar = is_string($_POST['voltar'] ?? null) ? $_POST['voltar'] : '/conta.php';
    $email = texto_post('email', 190);
    $senha = senha_post('senha');

    if (limite_excedido('login', LOGIN_MAX, LOGIN_JANELA_MIN)) {
        http_response_code(429);
        $erros[] = 'Muitas tentativas. Aguarde alguns minutos e tente de novo.';
    } else {
        $st = db()->prepare('SELECT id, nome, papel, senha_hash FROM usuarios WHERE email = ?');
        $st->execute([$email]);
        $u = $st->fetch();
        // Usuário inexistente gasta o mesmo custo de hash: não revela quais e-mails existem.
        $ok = $u ? password_verify($senha, $u['senha_hash']) : (password_hash($senha, PASSWORD_DEFAULT) && false);
        if ($ok) {
            if (password_needs_rehash($u['senha_hash'], PASSWORD_DEFAULT)) {
                db()->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?')
                    ->execute([password_hash($senha, PASSWORD_DEFAULT), $u['id']]);
            }
            autenticar($u);
            redirecionar($voltar);
        }
        registrar_tentativa('login');
        $erros[] = 'E-mail ou senha incorretos.';
    }
}

cabecalho('Entrar', ['privado' => true]);
?>
<section class="form-pagina">
  <header class="cabeca-pagina cabeca-pagina--curta">
    <p class="kicker">Bem-vinda de volta</p>
    <h1 class="titulo-pagina">Entrar</h1>
  </header>
  <?php lista_erros($erros); ?>
  <form class="formulario" method="post" action="/login.php">
    <?= csrf_campo() ?>
    <input type="hidden" name="voltar" value="<?= e($voltar) ?>">
    <label class="campo">E-mail
      <input type="email" name="email" required maxlength="190" autocomplete="username" value="<?= e($email) ?>">
    </label>
    <label class="campo">Senha
      <input type="password" name="senha" required autocomplete="current-password">
    </label>
    <button class="botao botao--roxo botao--grande botao--cheio">Entrar</button>
  </form>
  <p class="form-rodape">Ainda não tem conta? <a href="/registrar.php">Cadastre-se</a>.</p>
</section>
<?php rodape();
