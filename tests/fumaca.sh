#!/usr/bin/env bash
# Teste de fumaça do Constelar: confere, pelo HTTP, que o site no ar responde como deve.
# Uso: ./tests/fumaca.sh [URL]      (padrão: http://localhost:8080)
set -uo pipefail
BASE="${1:-http://localhost:8080}"
falhas=0

ok()    { printf '  ok     %s\n' "$1"; }
falha() { printf '  FALHA  %s\n' "$1"; falhas=$((falhas + 1)); }
codigo() { curl -s -o /dev/null -w '%{http_code}' "$@"; }
espera() { local c; c=$(codigo "${@:3}"); [ "$c" = "$2" ] && ok "$1 ($c)" || falha "$1: esperado $2, veio $c"; }

echo "Constelar em $BASE"

echo "Páginas públicas"
for p in / /cientistas.php /sobre.php /privacidade.php /jogo.php /login.php /registrar.php /saude.php /areas.css.php /assets/dados/figuras.json; do
  espera "GET $p" 200 "$BASE$p"
done
espera "página inexistente" 404 "$BASE/nao-existe"
espera "perfil inexistente" 404 "$BASE/cientista.php?id=999999"

echo "API do jogo"
json=$(curl -s "$BASE/api/cientistas.php")
if command -v php >/dev/null; then
  echo "$json" | php -r '$d = json_decode(stream_get_contents(STDIN), true); exit(is_array($d) && $d["areas"] && isset($d["cientistas"]) ? 0 : 1);' \
    && ok "JSON válido com áreas e cientistas" || falha "JSON da API inválido"
else
  case "$json" in *'"areas"'*'"cientistas"'*) ok "JSON com áreas e cientistas";; *) falha "JSON da API inválido";; esac
fi
case "$json" in *'"fonte"'*|*'"email"'*|*'autorizacao'*) falha "API expõe campos internos";; *) ok "API só expõe campos públicos";; esac
id=$(echo "$json" | grep -o '"id":[0-9]*,"numero"' | head -1 | grep -o '[0-9]*' | head -1)
[ -n "$id" ] && espera "perfil de cientista publicada" 200 "$BASE/cientista.php?id=$id"

echo "Proteções"
espera "painel exige login" 303 "$BASE/admin/"
espera "indicação exige login" 303 "$BASE/cadastro-cientista.php"
espera "minha conta exige login" 303 "$BASE/conta.php"
espera "POST sem token é recusado" 400 -X POST -d 'id=1&acao=excluir' "$BASE/admin/acao.php"
espera "logout só por POST" 405 "$BASE/logout.php"
c=$(codigo "$BASE/.env"); { [ "$c" = 403 ] || [ "$c" = 404 ]; } && ok "arquivos ocultos bloqueados ($c)" || falha "arquivo oculto acessível ($c)"
cab=$(curl -sI "$BASE/")
for h in Content-Security-Policy X-Content-Type-Options X-Frame-Options Referrer-Policy; do
  echo "$cab" | grep -qi "^$h:" && ok "cabeçalho $h" || falha "cabeçalho $h ausente"
done
echo "$cab" | grep -qi '^set-cookie:' && falha "visitante anônimo recebeu cookie" || ok "sem cookie para visitante anônimo"

echo
[ "$falhas" -eq 0 ] && echo "Tudo certo." || echo "$falhas verificação(ões) falharam."
exit "$falhas"
