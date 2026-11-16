<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

cabecalho('Privacidade e LGPD', ['descricao' => 'Como o Constelar trata dados pessoais, conforme a LGPD.']);
?>
<header class="cabeca-pagina">
  <p class="kicker">Lei nº 13.709/2018</p>
  <h1 class="titulo-pagina">Privacidade <em>e LGPD</em></h1>
  <p class="lede">O Constelar é um projeto escolar de divulgação científica. Esta página explica, em linguagem simples, que dados o site guarda e por quê.</p>
</header>

<section class="texto-longo texto-longo--topicos">
  <h2>Dados das cientistas</h2>
  <p>O site publica o nome, a área, a instituição e um resumo da trajetória profissional de cada cientista, sempre com a indicação das fontes. Só são exibidas cientistas que autorizaram o uso de seus dados. No caso de pessoas falecidas, usamos apenas informações públicas, de fontes citadas.</p>
  <p>Toda indicação fica oculta até ser revisada pela administração, que registra como a autorização foi obtida antes de publicar.</p>

  <h2>Dados de quem cria uma conta</h2>
  <p>Para indicar uma cientista é preciso criar uma conta. Guardamos o nome, o e-mail e a senha, que é armazenada de forma cifrada (nunca em texto legível). Esses dados servem apenas para identificar quem enviou cada indicação. Eles não são exibidos no site nem compartilhados com terceiros.</p>

  <h2>Cookies e progresso do jogo</h2>
  <p>O site usa um único cookie, criado quando você abre as páginas de login ou de cadastro, para manter a sua sessão. Não há cookies de publicidade nem de rastreamento.</p>
  <p>O progresso do jogo (as estrelas que você acendeu) fica guardado apenas no seu navegador. Ele não é enviado ao servidor e pode ser apagado a qualquer momento no menu do jogo, em “Novo jogo”.</p>

  <h2>Seus direitos</h2>
  <p>Se você é uma cientista que aparece no Constelar, ou representa uma delas, pode pedir a correção ou a retirada das informações a qualquer momento. Quem tem conta pode pedir a exclusão do cadastro. Os pedidos são atendidos pela administração do site<?php $contato = trim((string) getenv('CONTATO_EMAIL')); if (filter_var($contato, FILTER_VALIDATE_EMAIL)): ?>, pelo e-mail <a href="mailto:<?= e($contato) ?>"><?= e($contato) ?></a><?php else: ?> e devem ser feitos à equipe do projeto, na escola responsável<?php endif; ?>.</p>

  <h2>Segurança</h2>
  <p>O acesso ao painel de administração é restrito, as senhas são protegidas e os formulários têm proteção contra envios forjados. Como todo projeto escolar, o Constelar pode ter falhas: se encontrar uma, avise a equipe.</p>
</section>
<?php rodape();
