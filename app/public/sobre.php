<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$areas = areas();
$total = count(cientistas_publicadas());

cabecalho('Sobre o projeto', [
    'descricao' => 'Constelar: um jogo web para mapear e dar visibilidade a mulheres cientistas. Projeto para a SNCT 2026.',
]);
?>
<header class="cabeca-pagina">
  <p class="kicker">Sobre o projeto</p>
  <h1 class="titulo-pagina">Um jogo para mapear e dar visibilidade a <em>mulheres cientistas</em></h1>
</header>

<section class="texto-longo">
  <p class="lede">As mulheres ainda são minoria na ciência, e muitas cientistas brasileiras são pouco conhecidas pelo público, principalmente pelos estudantes. Sem exemplos próximos, fica mais difícil para meninas se imaginarem nessa carreira.</p>
  <p>O Constelar é um site em forma de jogo que apresenta mulheres cientistas. Cada cientista é uma estrela e cada área do conhecimento é uma constelação. Quem joga explora um observatório virtual, conversa com as estrelas e conhece as histórias que elas guardam. No começo o céu está apagado: é ouvindo cada história que ele se acende.</p>
</section>

<ul class="numeros-grandes">
  <li><strong><?= $total ?></strong><span>cientistas no álbum</span></li>
  <li><strong><?= count($areas) ?></strong><span>constelações</span></li>
  <li><strong>1</strong><span>homenageada: Duília de Mello</span></li>
</ul>

<section class="secao secao--duas">
  <div>
    <p class="kicker">Objetivos</p>
    <h2 class="titulo-secao">O que o <em>projeto</em> faz</h2>
  </div>
  <ol class="lista-numerada">
    <li><strong>Reúne</strong> cientistas, áreas do conhecimento e biografias em um banco de dados.</li>
    <li><strong>Recebe</strong> indicações de novas cientistas, publicadas só depois da aprovação da administração.</li>
    <li><strong>Apresenta</strong> cada cientista de forma interativa, em um jogo que roda no navegador.</li>
    <li><strong>Incentiva</strong> estudantes, especialmente meninas, a se interessarem por ciência.</li>
  </ol>
</section>

<section class="bloco-tinta">
  <span class="adesivo adesivo--amarelo ad-sn" aria-hidden="true">Homenageada</span>
  <div class="bloco-texto">
    <p class="kicker">Personalidade homenageada</p>
    <h2 class="citacao">Duília de Mello, a <em>“Mulher das Estrelas”</em>.</h2>
    <p>Astrônoma brasileira que descobriu a supernova SN 1997D e colabora com a NASA. Também atua na divulgação científica para incentivar crianças e jovens a seguir carreira em ciência. O observatório do jogo leva o seu nome.</p>
  </div>
  <?= simbolo('s-telescopio', 'bloco-desenho') ?>
</section>

<section class="secao secao--duas">
  <div>
    <p class="kicker">Como foi feito</p>
    <h2 class="titulo-secao">Do banco de dados <em>ao céu</em></h2>
  </div>
  <ol class="linha-tempo">
    <li><span>Etapa 1</span><strong>Pesquisa</strong> sobre mulheres cientistas brasileiras em fontes confiáveis.</li>
    <li><span>Etapa 2</span><strong>Banco de dados</strong> em MySQL com cientistas, áreas e biografias.</li>
    <li><span>Etapa 3</span><strong>Cadastro, login e painel</strong> do administrador em PHP.</li>
    <li><span>Etapa 4</span><strong>Jogo</strong> em JavaScript com a biblioteca Phaser: o mapa do observatório, as personagens e os diálogos vêm do banco de dados.</li>
    <li><span>Etapa 5</span><strong>Testes</strong> e apresentação na Semana Nacional de Ciência e Tecnologia de 2026.</li>
  </ol>
</section>

<section class="secao">
  <p class="kicker">Equipe</p>
  <h2 class="titulo-secao">Quem <em>fez</em></h2>
  <ul class="crachas">
    <?php foreach (['Cibelle', 'Fábio', 'Maria Antônia', 'Maria Vitória', 'Késia'] as $nome): ?>
      <li class="cracha"><span>Olá, meu nome é</span><strong><?= e($nome) ?></strong></li>
    <?php endforeach; ?>
  </ul>
</section>

<section class="secao secao--duas">
  <div>
    <p class="kicker">Dados pessoais</p>
    <h2 class="titulo-secao">Só com <em>autorização</em></h2>
  </div>
  <div class="texto-longo">
    <p>O Constelar só exibe cientistas que autorizaram o uso de seus dados, conforme a Lei Geral de Proteção de Dados Pessoais (LGPD). Cada indicação é revisada, e a autorização fica registrada antes da publicação.</p>
    <p><a class="botao" href="/privacidade.php">Ler a página de privacidade <?= simbolo('u-seta', 'ic') ?></a></p>
  </div>
</section>

<section class="secao">
  <p class="kicker">Referências</p>
  <h2 class="titulo-secao">Fontes <em>do projeto</em></h2>
  <ul class="referencias">
    <li>BRASIL. Lei nº 13.709, de 14 de agosto de 2018. Lei Geral de Proteção de Dados Pessoais (LGPD). Disponível em: planalto.gov.br.</li>
    <li>CATHOLIC UNIVERSITY OF AMERICA. Duilia de Mello profile. CatholicU Magazine, 2021. Disponível em: communications.catholic.edu.</li>
    <li>NASA. Profile: Dr. Duilia de Mello. Imagine the Universe!, 2008. Disponível em: imagine.gsfc.nasa.gov.</li>
    <li>PHASER STUDIO. Phaser 3 Documentation. Disponível em: docs.phaser.io.</li>
    <li>PHP GROUP. PHP Manual. Disponível em: php.net.</li>
    <li>REVISTA PESQUISA FAPESP. Dança das galáxias. Disponível em: revistapesquisa.fapesp.br.</li>
  </ul>
  <p class="miudo">As fontes de cada biografia aparecem na página da cientista. Tipografia: Archivo e Instrument Serif (SIL Open Font License). Motor do jogo: Phaser 3 (licença MIT).</p>
</section>
<?php rodape();
