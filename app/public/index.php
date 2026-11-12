<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$total = (int) db()->query("SELECT COUNT(*) FROM cientistas WHERE status = 'aprovado' AND autorizacao_uso = 1")->fetchColumn();
$areas = (int) db()->query('SELECT COUNT(*) FROM areas')->fetchColumn();

cabecalho('Início');
?>
<section class="hero">
  <h1>Cada cientista é uma estrela.</h1>
  <p>Explore um observatório virtual, encontre mulheres cientistas brasileiras e complete as constelações de cada área do conhecimento.</p>
  <p class="numeros"><strong><?= $total ?></strong> estrelas em <strong><?= $areas ?></strong> constelações</p>
  <a class="botao" href="/jogo.php">Começar a explorar</a>
</section>
<section class="cartao">
  <h2>Homenageada: Duília de Mello</h2>
  <p>Astrônoma brasileira conhecida como "Mulher das Estrelas". Descobriu a supernova SN 1997D, colabora com a NASA e atua na divulgação científica para incentivar crianças e jovens a seguir carreira em ciência.</p>
</section>
<section class="cartao">
  <h2>Conhece uma cientista que deveria estar aqui?</h2>
  <p>Crie uma conta e indique. Toda indicação passa pela revisão da administração, e só são publicadas cientistas que autorizaram o uso de seus dados (LGPD).</p>
  <a class="botao secundario" href="/cadastro-cientista.php">Indicar cientista</a>
</section>
<?php rodape();
