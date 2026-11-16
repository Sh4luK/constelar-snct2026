<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/bootstrap.php';

if (http_response_code() === 200) {
    http_response_code(404);
}
$proibido = http_response_code() === 403;

cabecalho($proibido ? 'Acesso restrito' : 'Página não encontrada', ['privado' => true]);
?>
<section class="erro-central">
  <?= simbolo('s-lume', 'erro-lume') ?>
  <p class="kicker">Erro <?= (int) http_response_code() ?></p>
  <?php if ($proibido): ?>
    <h1 class="titulo-pagina">Área <em>restrita</em></h1>
    <p class="lede">Esta parte do observatório é só para a administração.</p>
  <?php else: ?>
    <h1 class="titulo-pagina">Esta estrela não está <em>no mapa</em></h1>
    <p class="lede">O endereço que você abriu não existe ou saiu do ar.</p>
  <?php endif; ?>
  <div class="grupo-botoes">
    <a class="botao botao--roxo" href="/">Voltar ao início</a>
    <a class="botao" href="/cientistas.php">Ver o álbum</a>
  </div>
</section>
<?php rodape();
