<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit(1);
}

require __DIR__ . '/../src/bootstrap.php';

$email = trim((string) getenv('ADMIN_EMAIL'));
$senha = (string) getenv('ADMIN_PASSWORD');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 10) {
    fwrite(STDERR, "[constelar] ADMIN_EMAIL inválido ou ADMIN_PASSWORD com menos de 10 caracteres.\n");
    exit(1);
}

for ($i = 1; ; $i++) {
    try {
        $pdo = db();
        break;
    } catch (PDOException $e) {
        if ($i >= 30) {
            fwrite(STDERR, "[constelar] Banco indisponível: {$e->getMessage()}\n");
            exit(1);
        }
        sleep(2);
    }
}

$st = $pdo->prepare('SELECT id, senha_hash FROM usuarios WHERE email = ?');
$st->execute([$email]);
$u = $st->fetch();

if ($u === false) {
    $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash, papel) VALUES ('Administração', ?, ?, 'admin')")
        ->execute([$email, password_hash($senha, PASSWORD_DEFAULT)]);
    echo "[constelar] Administrador criado: {$email}\n";
} elseif (!password_verify($senha, $u['senha_hash'])) {
    $pdo->prepare("UPDATE usuarios SET senha_hash = ?, papel = 'admin' WHERE id = ?")
        ->execute([password_hash($senha, PASSWORD_DEFAULT), $u['id']]);
    echo "[constelar] Senha do administrador atualizada: {$email}\n";
}
