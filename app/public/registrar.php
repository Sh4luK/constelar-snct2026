<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$erros = [];
$nome = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_post_com_csrf();
    $nome  = texto_post('nome', 120);
    $email = texto_post('email', 190);
    $senha = is_string($_POST['senha'] ?? null) ? $_POST['senha'] : '';

    if (mb_strlen($nome) < 2) {
        $erros[] = 'Informe seu nome.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'E-mail inválido.';
    }
    if (strlen($senha) < 8 || strlen($senha) > 72) {
        $erros[] = 'A senha deve ter entre 8 e 72 caracteres.';
    }

    if (!$erros) {
        try {
            db()->prepare('INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)')
                ->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT)]);
            autenticar(['id' => db()->lastInsertId(), 'nome' => $nome, 'papel' => 'usuario']);
            flash('Conta criada. Agora você pode indicar cientistas.');
            redirecionar('/cadastro-cientista.php');
        } catch (PDOException $ex) {
            if ($ex->errorInfo[1] !== 1062) {
                throw $ex;
            }
            $erros[] = 'Este e-mail já está cadastrado.';
        }
    }
}

cabecalho('Criar conta');
?>
<section class="cartao estreito">
  <h1>Criar conta</h1>
  <?php foreach ($erros as $er): ?><p class="erro"><?= e($er) ?></p><?php endforeach; ?>
  <form method="post" action="/registrar.php">
    <?= csrf_campo() ?>
    <label>Nome <input name="nome" required maxlength="120" value="<?= e($nome) ?>" autocomplete="name"></label>
    <label>E-mail <input type="email" name="email" required maxlength="190" value="<?= e($email) ?>" autocomplete="email"></label>
    <label>Senha (mín. 8 caracteres) <input type="password" name="senha" required minlength="8" maxlength="72" autocomplete="new-password"></label>
    <button class="botao">Criar conta</button>
  </form>
  <p>Já tem conta? <a href="/login.php">Entrar</a>.</p>
</section>
<?php rodape();
