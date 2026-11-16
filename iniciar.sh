#!/usr/bin/env bash
# Sobe o Constelar com um comando: cria o .env (com senhas aleatórias) se faltar,
# constrói a imagem, inicia os containers e espera o site responder.
set -euo pipefail
cd "$(dirname "$0")"

DOCKER=(docker)
if ! docker info >/dev/null 2>&1; then
  DOCKER=(sudo docker)
fi

senha() { LC_ALL=C tr -dc 'A-Za-z0-9' </dev/urandom | head -c "${1:-24}" || true; }

if [ ! -f .env ]; then
  sed -e "s/^DB_PASSWORD=.*/DB_PASSWORD=$(senha)/" \
      -e "s/^DB_ROOT_PASSWORD=.*/DB_ROOT_PASSWORD=$(senha)/" \
      -e "s/^ADMIN_PASSWORD=.*/ADMIN_PASSWORD=$(senha 16)/" \
      .env.example > .env
  chmod 600 .env
  echo "→ .env criado com senhas aleatórias."
fi

# shellcheck disable=SC1091
set -a; . ./.env; set +a
PORTA="${APP_PORT:-8080}"

echo "→ Construindo e iniciando (a primeira vez pode levar alguns minutos)…"
"${DOCKER[@]}" compose up -d --build

echo -n "→ Aguardando o site"
for _ in $(seq 1 120); do
  if curl -fsS "http://localhost:${PORTA}/saude.php" >/dev/null 2>&1; then
    echo
    echo
    echo "  Constelar no ar:  http://localhost:${PORTA}"
    echo "  Jogo (modo feira): http://localhost:${PORTA}/jogo.php?feira=1"
    echo "  Painel:           http://localhost:${PORTA}/admin/   (login: ${ADMIN_EMAIL}; senha em .env)"
    exit 0
  fi
  echo -n "."
  sleep 3
done

echo
echo "O site não respondeu. Veja o motivo com:"
echo "  ${DOCKER[*]} compose ps"
echo "  ${DOCKER[*]} compose logs --tail 40 db app"
echo "Se o log do banco citar 'x86-64-v2', o processador é antigo: defina DB_IMAGE=mariadb:11.4 no .env,"
echo "apague o volume (${DOCKER[*]} compose down -v) e rode ./iniciar.sh de novo."
exit 1
