<?php
declare(strict_types=1);

/**
 * Constelar — ponto de entrada comum de todas as páginas.
 *
 *   banco.php      conexão e consultas compartilhadas
 *   http.php       saída segura, redirecionamento, mensagens, erros
 *   seguranca.php  sessão, CSRF, login, limites
 *   arte.php       desenhos em SVG (sprites, constelações, figurinhas)
 *   acervo.php     busca, filtros, temas e estatísticas do acervo
 *   layout.php     cabeçalho e rodapé
 */

const APP_NOME = 'Constelar';

require __DIR__ . '/banco.php';
require __DIR__ . '/http.php';
require __DIR__ . '/seguranca.php';
require __DIR__ . '/arte.php';
require __DIR__ . '/acervo.php';
require __DIR__ . '/fotos.php';
require __DIR__ . '/layout.php';

set_exception_handler('tratar_erro_fatal');
