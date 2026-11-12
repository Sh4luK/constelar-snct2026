<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

exigir_post_com_csrf();
$admin = exigir_admin();

$id = (int) ($_POST['id'] ?? 0);
$acao = $_POST['acao'] ?? '';
$volta = in_array($_POST['volta'] ?? '', ['pendente', 'aprovado', 'rejeitado'], true) ? $_POST['volta'] : 'pendente';

$novo = ['aprovar' => 'aprovado', 'rejeitar' => 'rejeitado'][$acao] ?? null;
$rotulo = ['aprovado' => 'aprovada', 'rejeitado' => 'rejeitada'];

if ($novo !== null) {
    $st = db()->prepare('UPDATE cientistas SET status = ?, revisado_por = ?, revisado_em = NOW() WHERE id = ?');
    $st->execute([$novo, $admin['id'], $id]);
    flash($st->rowCount() ? 'Cientista ' . $rotulo[$novo] . '.' : 'Registro não encontrado.');
} elseif ($acao === 'excluir') {
    $st = db()->prepare('DELETE FROM cientistas WHERE id = ?');
    $st->execute([$id]);
    flash($st->rowCount() ? 'Registro excluído.' : 'Registro não encontrado.');
} else {
    http_response_code(400);
    exit('Ação inválida.');
}

redirecionar('/admin/?status=' . $volta);
