# Constelar

Acervo pesquisável e colaborativo de mulheres cientistas brasileiras, apoiado em banco de dados MySQL: busca com filtros, temas, estatísticas, exportação (CSV/JSON), indicações revisadas pela administração e registro de autorização (LGPD). Como forma de explorar o acervo, há um jogo em que cada cientista é uma estrela e cada área do conhecimento, uma constelação. Projeto para a SNCT 2026, em homenagem a Duília de Mello, a "Mulher das Estrelas".

PHP 8.3 (Apache) · MySQL 8.4 · Phaser 3.90 · tudo servido localmente (funciona sem internet depois de construído).

## Rodar

```sh
./iniciar.sh
```

O script cria o `.env` com senhas aleatórias (se ainda não existir), constrói a imagem, sobe os containers e espera o site responder em http://localhost:8080. O login do painel é o `ADMIN_EMAIL` do `.env`; a senha está em `ADMIN_PASSWORD`.

Sem o script: `cp .env.example .env`, troque as senhas e rode `docker compose up -d`. A imagem é reconstruída a cada `up`, então depois de um `git pull` basta repetir o comando.

| Tarefa | Comando |
|---|---|
| Ver o que está acontecendo | `docker compose ps` e `docker compose logs --tail 40 db app` |
| Conferir o site no ar | `./tests/fumaca.sh` |
| Apagar tudo e recomeçar | `docker compose down -v` |
| Cópia de segurança do banco | `docker compose exec db sh -c 'mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' > copia.sql` |

### Na feira

Abra `http://localhost:8080/jogo.php?feira=1` nos computadores do estande: depois de 2 minutos sem uso o jogo recomeça sozinho para o próximo visitante, e o botão de sair para o site some. No menu do jogo há tela cheia, som e "Novo jogo".

### Publicar na internet (para outra pessoa acessar)

Com o site rodando no seu computador, um túnel da Cloudflare cria um endereço público com HTTPS, sem conta e sem abrir portas no roteador. O endereço vale enquanto o computador e o túnel estiverem ligados.

```powershell
winget install --id Cloudflare.cloudflared     # uma vez (Windows)
cloudflared tunnel --url http://localhost:8080
```

O comando mostra um endereço `https://….trycloudflare.com`: é esse que se envia. Cada vez que o túnel é reiniciado o endereço muda. Antes de publicar, troque `ADMIN_PASSWORD` no `.env` por uma senha forte e rode `docker compose up -d`.

### Deixar no ar o tempo todo (Railway)

O projeto já vem preparado para o [Railway](https://railway.com) (`railway.json`). O nome dos menus pode variar um pouco no site deles.

1. Entre em railway.com com a conta do GitHub.
2. **New Project → Deploy from GitHub repo →** `Sh4luK/constelar-snct2026`. Se o código não estiver no branch principal, abra o serviço criado, vá em **Settings → Source** e escolha o branch do projeto.
3. No mesmo projeto: **+ New → Database → MySQL**.
4. No serviço do site, aba **Variables**, crie (os valores com `${{…}}` são copiados do MySQL pelo próprio Railway):

   | Variável | Valor |
   |---|---|
   | `MYSQLHOST` | `${{MySQL.MYSQLHOST}}` |
   | `MYSQLPORT` | `${{MySQL.MYSQLPORT}}` |
   | `MYSQLUSER` | `${{MySQL.MYSQLUSER}}` |
   | `MYSQLPASSWORD` | `${{MySQL.MYSQLPASSWORD}}` |
   | `MYSQLDATABASE` | `${{MySQL.MYSQLDATABASE}}` |
   | `ADMIN_EMAIL` | o e-mail do administrador |
   | `ADMIN_PASSWORD` | uma senha forte (10+ caracteres) |
   | `CONFIAR_PROXY` | `1` |

5. Ainda no serviço do site: **Settings → Volumes → Add Volume**, com caminho `/var/www/dados/fotos` (guarda as fotos entre atualizações).
6. **Settings → Networking → Generate Domain.** Se pedir uma porta, use a que o Railway sugerir (o site se ajusta à variável `PORT`).
7. Aguarde o deploy terminar (a primeira vez leva alguns minutos) e abra o endereço `https://….up.railway.app`.

A cada `git push` no branch escolhido, o Railway publica a nova versão sozinho. O banco e as fotos continuam. O plano de teste do Railway tem crédito limitado: quando acaba, o site sai do ar até que se escolha um plano pago.

### Se o banco não subir

Em computadores antigos, o container `db` fica "unhealthy" e o log mostra `Fatal glibc error: CPU does not support x86-64-v2`: o processador não tem as instruções que a imagem `mysql:8.4` exige. Nesse caso, defina no `.env`:

```
DB_IMAGE=mariadb:11.4
```

e recrie o banco (`docker compose down -v` e `./iniciar.sh`). O MariaDB é compatível com tudo o que o projeto usa; a suíte de testes passa nos dois.

Na primeira subida o banco pode levar alguns minutos em disco lento; o Docker espera até 5 minutos antes de desistir.

## O que tem

| Endereço | Função |
|---|---|
| `/` | Início: busca no acervo, áreas, temas, entradas recentes e acesso ao jogo. |
| `/cientistas.php` | Acervo: busca por texto, filtros por área e tema, ordenação, paginação; vistas em tabela, cartões ou álbum; exportação do resultado. |
| `/cientista.php?id=N` | Ficha de uma cientista: biografia, curiosidade, temas e fontes. |
| `/dados.php` | Painel de dados: indicadores, gráficos por área, tema e instituição, dados abertos e modelo do banco. |
| `/exportar.php?formato=csv\|json` | Exporta o acervo (aceita os mesmos filtros do acervo: `q`, `area`, `tema`, `ordem`). |
| `/jogo.php` | Modo jogo: observatório, constelações, guia (Lume), álbum, mapa, desafio e certificado. |
| `/sobre.php`, `/privacidade.php` | O projeto, a equipe, as referências e o tratamento de dados (LGPD). |
| `/registrar.php`, `/login.php`, `/conta.php` | Conta, minhas indicações e troca de senha. |
| `/cadastro-cientista.php` | Indicar uma cientista (fica pendente até a revisão). |
| `/admin/` | Painel: busca, aprovar (com registro da autorização), rejeitar com recado, editar (inclusive temas), despublicar, excluir. |
| `/admin/areas.php` | Criar e editar constelações (nome, cor, símbolo, ordem). |
| `/api/cientistas.php` | Dados do jogo. Só devolve cientistas aprovadas e com autorização. |

### Jogo

- Andar: setas, WASD, clique ou toque. Conversar: `E`, Espaço, Enter ou toque. `M` mapa, `B` álbum, `Esc` menu.
- O progresso fica só no navegador de quem joga.
- Desenhos próprios em SVG (`app/src/sprites.php`), usados no site e no jogo. Sons sintetizados, sem arquivos de áudio.
- Em computador fraco o jogo troca sozinho para o modo Canvas e desliga desfoques e partículas. Para forçar: `?render=canvas` e `?leve=1`.
- `?debug=1` expõe `window.__constelar` para testes automatizados.

## Estrutura

```
iniciar.sh                 sobe tudo com um comando
docker-compose.yml         serviços db e app
docker/                    Dockerfile, Apache, PHP, entrypoint
tests/fumaca.sh            teste de fumaça por HTTP
app/
  bin/migrar.php           aplica as migrações na subida do container
  bin/seed-admin.php       cria/atualiza o administrador do .env
  db/migracoes/*.sql       esquema, acervo e temas, em ordem numérica
  src/                     banco, acervo (busca/estatísticas), http, seguranca, arte (SVG), layout
  public/                  páginas, admin/, api/, assets/
    assets/js/jogo/        nucleo, audio, ui, mundo, main (módulos ES)
    assets/dados/figuras.json   desenho de cada constelação (site e jogo)
```

Para mudar o banco, crie um novo arquivo em `app/db/migracoes/` com o próximo número; ele roda sozinho na próxima subida. Não edite migrações já aplicadas.

## Fotos

- **Filtro padronizado.** Toda foto aparece em duas cores, preto e a cor da área (azul na Astronomia, amarelo na Química…), com retícula de impressão. O filtro é aplicado na hora de exibir; a foto guardada é neutra, então mudar a cor ou a área de uma cientista muda a foto junto.
- **Envio.** O formulário de indicação e a edição no painel aceitam JPG, PNG ou WEBP de até 5 MB, com crédito obrigatório, e mostram a prévia já com o filtro. O servidor redecodifica toda imagem com a biblioteca GD: sai sempre um JPEG quadrado de 640 px, sem metadados (inclusive localização de GPS) e sem nada além dos pixels. Cientista sem foto continua com a personagem ilustrada.
- **Onde ficam.** No volume `fotos_data`, servidas em `/fotos/`, onde só arquivos `.jpg` gerados pelo sistema podem ser abertos e o PHP fica desligado. Para fazer cópia de segurança: `docker compose cp app:/var/www/dados/fotos ./copia-fotos`.
- **Acervo inicial.** Seis fotos (Duília de Mello, Beatriz Barbuy, Thaisa Storchi Bergmann, Rosaly Lopes, Márcia Barbosa e Sonia Guimarães) já vêm no projeto, em `app/db/fotos/`. Outras 13 estão listadas em `app/db/fotos-acervo.json` e são baixadas da Wikimedia Commons, em segundo plano, na primeira vez que o site sobe com internet. Todas têm licença livre, que é conferida no download, e o crédito aparece na página da cientista. Para forçar uma nova tentativa: `docker compose exec app php bin/importar-fotos.php`.
- **Sem foto livre encontrada:** Elisa Frota-Pessoa, Eloisa Mano, Blanka Wladislaw, Mayana Zatz, Carolina Bori e Enedina Alves Marques. Para essas, envie pelo painel (Editar → Foto) uma imagem com autorização de uso e o crédito.

## Banco de dados

`areas` 1:N `cientistas` (com foto, crédito e fonte da foto) N:N `temas` (tabela `cientista_tema`); `usuarios` guarda quem indicou e quem revisou cada registro; `tentativas_login` limita abusos; `migracoes` controla as versões do esquema. A busca usa consultas preparadas com `LIKE` em nome, instituição, biografia e curiosidade.

## Segurança

PDO com consultas preparadas; senhas com `password_hash`; token CSRF em todo POST; sessão regenerada no login e expirada após 2 horas sem uso; limite de tentativas de login por IP; saída sempre escapada; CSP sem scripts ou estilos inline; papel de administrador reconferido no banco a cada acesso; visitante anônimo não recebe cookie.

## Pendências antes do uso público

- **Autorizações (LGPD).** O acervo inicial é de demonstração. As cientistas vivas estão marcadas no painel como "Autorização a confirmar": registre a autorização de cada uma (em "Editar") ou despublique.
- **Conferir os textos.** As biografias foram escritas a partir de fontes públicas e conferidas por busca, mas a equipe deve revisar cada uma com as fontes citadas.
- **HTTPS.** Para publicar na internet, coloque um proxy reverso com HTTPS na frente; o cookie de sessão passa a ser `Secure` automaticamente.
- **Contato.** Preencha `CONTATO_EMAIL` no `.env` para que a página de privacidade informe onde pedir correção ou retirada de dados.
- Não há recuperação de senha por e-mail (o administrador é redefinido pelo `.env`).

## Créditos

Tipografia: Archivo e Instrument Serif (SIL Open Font License; licenças em `app/public/assets/fonts/`). Motor do jogo: Phaser 3 (MIT).
