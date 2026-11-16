<?php
declare(strict_types=1);

/** Cria (ou atualiza a senha de) o administrador definido em ADMIN_EMAIL / ADMIN_PASSWORD. */

if (PHP_SAPI !== 'cli') {
    exit(1);
}

require __DIR__ . '/../src/banco.php';

$email = trim((string) getenv('ADMIN_EMAIL'));
$senha = (string) getenv('ADMIN_PASSWORD');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 10) {
    fwrite(STDERR, "[constelar] ADMIN_EMAIL inválido ou ADMIN_PASSWORD com menos de 10 caracteres.\n");
    exit(1);
}

$pdo = db();
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
