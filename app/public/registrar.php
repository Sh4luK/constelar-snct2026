<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

// O formulário precisa do cookie de sessão antes de qualquer saída (o token CSRF mora nela).
csrf_token();

if (usuario_atual()) {
    redirecionar('/conta.php');
}

$erros = [];
$nome = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_post_com_csrf();
    $nome  = texto_post('nome', 120);
    $email = texto_post('email', 190);
    $senha = senha_post('senha');

    if (mb_strlen($nome) < 2) {
        $erros[] = 'Informe o seu nome.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'E-mail inválido.';
    }
    if (strlen($senha) < 8 || strlen($senha) > 72) {
        $erros[] = 'A senha deve ter entre 8 e 72 caracteres.';
    }
    if (($_POST['termos'] ?? '') !== '1') {
        $erros[] = 'É preciso concordar com o uso dos dados descrito na página de privacidade.';
    }
    // Limite alto de propósito: uma turma inteira pode se cadastrar da mesma rede.
    if (!$erros && limite_excedido('registro', 40, 60)) {
        http_response_code(429);
        $erros[] = 'Muitos cadastros a partir desta rede. Tente de novo mais tarde.';
    }

    if (!$erros) {
        try {
            db()->prepare('INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)')
                ->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT)]);
            $id = (int) db()->lastInsertId(); // antes de qualquer outro INSERT
            registrar_tentativa('registro');
            autenticar(['id' => $id, 'nome' => $nome, 'papel' => 'usuario']);
            flash('Conta criada. Agora você pode indicar cientistas.');
            redirecionar('/cadastro-cientista.php');
        } catch (PDOException $ex) {
            if (($ex->errorInfo[1] ?? 0) !== 1062) {
                throw $ex;
            }
            $erros[] = 'Este e-mail já está cadastrado.';
        }
    }
}

cabecalho('Criar conta', ['privado' => true]);
?>
<section class="form-pagina">
  <header class="cabeca-pagina cabeca-pagina--curta">
    <p class="kicker">Participe</p>
    <h1 class="titulo-pagina">Criar <em>conta</em></h1>
  </header>
  <?php lista_erros($erros); ?>
  <form class="formulario" method="post" action="/registrar.php">
    <?= csrf_campo() ?>
    <label class="campo">Nome
      <input name="nome" required maxlength="120" autocomplete="name" value="<?= e($nome) ?>">
    </label>
    <label class="campo">E-mail
      <input type="email" name="email" required maxlength="190" autocomplete="email" value="<?= e($email) ?>">
    </label>
    <label class="campo">Senha <span class="campo-ajuda">8 caracteres ou mais</span>
      <input type="password" name="senha" required minlength="8" maxlength="72" autocomplete="new-password">
    </label>
    <label class="check"><input type="checkbox" name="termos" value="1" required>
      <span>Concordo que meu nome e e-mail sejam guardados para identificar as minhas indicações, como explica a <a href="/privacidade.php">página de privacidade</a>.</span></label>
    <button class="botao botao--roxo botao--grande botao--cheio">Criar conta</button>
  </form>
  <p class="form-rodape">Já tem conta? <a href="/login.php">Entrar</a>.</p>
</section>
<?php rodape();
