# Constelar — protótipo

Jogo web que dá visibilidade a mulheres cientistas brasileiras. Cada cientista é uma estrela; cada área do conhecimento, uma constelação. Projeto para a SNCT 2026, em homenagem a Duília de Mello.

Stack: PHP 8.3 (Apache) + MySQL 8.4 + Phaser 3.90 (servido localmente, funciona sem internet).

## Rodar

```sh
cp .env.example .env      # troque TODAS as senhas (ADMIN_PASSWORD ≥ 10 caracteres)
docker compose up -d --build
```

Acesse http://localhost:8080. O administrador é criado na subida do container a partir de `ADMIN_EMAIL`/`ADMIN_PASSWORD`.

Após alterar código em `app/`, rode `docker compose up -d --build` de novo. Para recriar o banco do zero: `docker compose down -v`.

## Funcionalidades

| Página | Função |
|---|---|
| `/jogo.php` | Observatório virtual (Phaser): mover com setas/WASD, clique ou toque; `E`/Espaço/Enter ou toque para conversar. Conversar acende a estrela; acender todas completa a constelação. Progresso salvo no navegador. |
| `/cientistas.php` | Lista das cientistas publicadas. |
| `/registrar.php`, `/login.php` | Conta de usuário. |
| `/cadastro-cientista.php` | Indicação de cientista (exige login e declaração de autorização LGPD). Entra como `pendente`. |
| `/admin/` | Painel do administrador: aprovar, rejeitar, excluir. |
| `/api/cientistas.php` | JSON consumido pelo jogo. Só retorna `status = aprovado` **e** `autorizacao_uso = 1`. |

## Estrutura

```
docker-compose.yml   docker/        (Dockerfile, Apache, PHP, entrypoint)
db/init.sql          esquema + áreas + cientistas de demonstração
app/src/             bootstrap (PDO, sessão, CSRF, auth, layout)
app/bin/             seed do administrador
app/public/          páginas, API, admin, assets (jogo.js, phaser.min.js, CSS)
```

## Visual

- Moldura azul elétrica com página em "papel", tipografia display condensada (Archivo variável) misturada com serifa (Instrument Serif), adesivos rotacionados e mascote em SVG. Fontes servidas localmente (licença OFL em `app/public/assets/fonts/`).
- Vidro em botões e menus: classe `.glass` (+ `--azul`, `--escuro`, `--claro`, `--interativo`) com `backdrop-filter`, reflexo que segue o ponteiro (`glass.js`) e resposta ao toque com efeito de mola. Sem suporte a `backdrop-filter` ou com `prefers-reduced-transparency`, vira fundo sólido. `expo-glass-effect` é exclusivo de React Native/iOS e não se aplica à web.
- No jogo, HUD, diálogo, avisos e botão de voltar são HTML com vidro sobre o canvas (texto nítido em telas de alta densidade).

## Segurança aplicada

PDO com prepared statements; `password_hash`; CSRF em todo POST; `session_regenerate_id` no login; limite de tentativas de login por IP; saída escapada (`htmlspecialchars`) e CSP sem scripts inline; redirecionamento só para caminhos locais.

## Pendências antes de publicar

- Servir atrás de HTTPS (proxy reverso). O cookie de sessão vira `Secure` automaticamente quando a requisição chega por HTTPS.
- Os 9 registros em `db/init.sql` são dados de demonstração compilados de fontes públicas: revisar com fontes e registrar a autorização de cada cientista viva (LGPD) antes do uso público.
- Sem recuperação de senha nem verificação de e-mail.
