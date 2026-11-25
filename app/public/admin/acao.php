<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

exigir_post_com_csrf();
$admin = exigir_admin();

$id = (int) ($_POST['id'] ?? 0);
$acao = is_string($_POST['acao'] ?? null) ? $_POST['acao'] : '';
$volta = in_array($_POST['volta'] ?? '', ['pendente', 'aprovado', 'rejeitado'], true) ? $_POST['volta'] : 'pendente';
$pdo = db();

switch ($acao) {
    case 'aprovar':
        $registro = texto_post('registro', 300);
        if (mb_strlen($registro) < 5) {
            flash('Para publicar, registre como a autorização da cientista foi obtida.', 'erro');
            break;
        }
        $st = $pdo->prepare(
            "UPDATE cientistas SET status = 'aprovado', autorizacao_uso = 1, autorizacao_registro = ?, nota_revisao = NULL,
                    revisado_por = ?, revisado_em = NOW() WHERE id = ?"
        );
        $st->execute([$registro, $admin['id'], $id]);
        flash($st->rowCount() ? 'Cientista publicada.' : 'Registro não encontrado.', $st->rowCount() ? 'ok' : 'erro');
        break;

    case 'rejeitar':
        $nota = texto_post('nota', 300);
        $st = $pdo->prepare(
            "UPDATE cientistas SET status = 'rejeitado', nota_revisao = NULLIF(?, ''), revisado_por = ?, revisado_em = NOW() WHERE id = ?"
        );
        $st->execute([$nota, $admin['id'], $id]);
        flash($st->rowCount() ? 'Indicação rejeitada.' : 'Registro não encontrado.', $st->rowCount() ? 'ok' : 'erro');
        break;

    case 'pendente':
        $st = $pdo->prepare("UPDATE cientistas SET status = 'pendente', revisado_por = ?, revisado_em = NOW() WHERE id = ?");
        $st->execute([$admin['id'], $id]);
        flash($st->rowCount() ? 'Cientista devolvida para revisão (fora do site).' : 'Registro não encontrado.', $st->rowCount() ? 'ok' : 'erro');
        break;

    case 'excluir':
        $st = $pdo->prepare('SELECT foto FROM cientistas WHERE id = ?');
        $st->execute([$id]);
        $foto = $st->fetchColumn() ?: null;
        $st = $pdo->prepare('DELETE FROM cientistas WHERE id = ?');
        $st->execute([$id]);
        if ($st->rowCount()) {
            apagar_foto($foto);
        }
        flash($st->rowCount() ? 'Registro excluído.' : 'Registro não encontrado.', $st->rowCount() ? 'ok' : 'erro');
        break;

    default:
        http_response_code(400);
        exit('Ação inválida.');
}

redirecionar('/admin/?status=' . $volta);
