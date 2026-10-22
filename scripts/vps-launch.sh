#!/usr/bin/env bash
# Keep Focus launch helper. Runs ON THE VPS, as root, one step at a time:
#
#   bash vps-launch.sh backup   copy the database and .env to /root/keep-focus-backups
#   bash vps-launch.sh nginx    create the Nginx site for the domain and request the SSL certificate
#   bash vps-launch.sh env      update .env for Keep Focus (keeps APP_KEY and Google credentials)
#   bash vps-launch.sh check    after the deploy: migrations, legacy copy summary and HTTPS
#
# Every step can be run again safely. DNS and the Google Cloud Console are done by hand (docs/deploy.md).
set -euo pipefail

DOMAIN=keep-focus.luvcode.tech
APP_DIR=/opt/projects/Keep-Training-app
APP_PORT=8082
BACKUP_DIR=/root/keep-focus-backups
NGINX_SITE=/etc/nginx/sites-available/keep-focus

step() { echo -e "\n\033[1;34m▶ $*\033[0m"; }
ok() { echo -e "\033[0;32m✔ $*\033[0m"; }
fail() { echo -e "\033[1;31m✖ $*\033[0m" >&2; exit 1; }

[ "$(id -u)" -eq 0 ] || fail "Rode como root."
cd "$APP_DIR" || fail "Pasta $APP_DIR não encontrada."

backup() {
    step "Backup do banco e do .env"
    mkdir -p "$BACKUP_DIR"
    local stamp
    stamp=$(date +%Y%m%d-%H%M%S)
    cp database/database.sqlite "$BACKUP_DIR/database-$stamp.sqlite"
    cp .env "$BACKUP_DIR/env-$stamp"
    chmod 600 "$BACKUP_DIR/env-$stamp"
    ok "Salvos em $BACKUP_DIR (database-$stamp.sqlite, env-$stamp)"
}

nginx_site() {
    step "Conferindo o DNS de $DOMAIN"
    local resolved server
    resolved=$(getent ahostsv4 "$DOMAIN" | awk 'NR==1 {print $1}')
    server=$(curl -4 -fsS https://api.ipify.org || true)
    [ -n "$resolved" ] || fail "$DOMAIN ainda não resolve. Crie o registro A e espere propagar."
    if [ -n "$server" ] && [ "$resolved" != "$server" ]; then
        fail "$DOMAIN aponta para $resolved, mas este servidor é $server."
    fi
    ok "$DOMAIN -> $resolved"

    step "Site do Nginx"
    if [ -f "$NGINX_SITE" ]; then
        ok "$NGINX_SITE já existe; não foi alterado."
    else
        cat > "$NGINX_SITE" <<NGINX
server {
    listen 80;
    server_name $DOMAIN;

    location / {
        proxy_pass http://127.0.0.1:$APP_PORT;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}
NGINX
        ok "Criado $NGINX_SITE"
    fi
    ln -sf "$NGINX_SITE" /etc/nginx/sites-enabled/keep-focus
    nginx -t
    systemctl reload nginx
    ok "Nginx recarregado"

    step "Certificado SSL"
    if certbot certificates 2>/dev/null | grep -q "Domains: .*$DOMAIN"; then
        ok "Certificado de $DOMAIN já existe."
    else
        certbot --nginx -d "$DOMAIN" --redirect
    fi
    curl -fsSI "https://$DOMAIN" -o /dev/null && ok "https://$DOMAIN responde" || echo "Aviso: https://$DOMAIN ainda não respondeu."
}

set_env() {
    local key=$1 value=$2
    if grep -q "^$key=" .env; then
        sed -i "s|^$key=.*|$key=$value|" .env
    else
        echo "$key=$value" >> .env
    fi
    echo "  $key=$value"
}

env_file() {
    step "Conferindo as credenciais do Google no .env"
    grep -q '^GOOGLE_CLIENT_ID=.\+' .env || fail "GOOGLE_CLIENT_ID está vazio ou não existe no .env. Adicione antes de continuar."
    grep -q '^GOOGLE_CLIENT_SECRET=.\+' .env || fail "GOOGLE_CLIENT_SECRET está vazio ou não existe no .env. Adicione antes de continuar."
    ok "Credenciais do Google presentes (não foram alteradas)"

    backup

    step "Atualizando o .env para o Keep Focus"
    set_env APP_NAME '"Keep Focus"'
    set_env APP_URL "https://$DOMAIN"
    set_env APP_LOCALE pt_BR
    set_env APP_FALLBACK_LOCALE en
    set_env APP_FAKER_LOCALE pt_BR
    set_env APP_TIMEZONE America/Sao_Paulo
    set_env APP_VERSION 1.0.0
    set_env SESSION_SECURE_COOKIE true
    set_env GOOGLE_REDIRECT_URI "https://$DOMAIN/auth/google/callback"
    ok "APP_KEY, APP_PORT e as credenciais do Google foram mantidos."

    if [ "${1:-}" = "--new-key" ]; then
        step "Gerando um APP_KEY novo"
        ./vendor/bin/sail artisan key:generate --force
    fi

    echo
    echo "A produção usa a configuração em cache: o app só passa a usar este .env quando o deploy"
    echo "refizer o cache. Faça o merge da KT-20 na main agora."
}

check() {
    step "Migrations"
    ./vendor/bin/sail artisan migrate:status | tail -n 10

    step "Cópia dos suplementos antigos"
    grep "Legacy supplements copied" storage/logs/laravel.log | tail -n 1 || echo "Resumo não encontrado no log (a migration ainda não rodou?)."

    step "HTTPS"
    local code
    code=$(curl -s -o /dev/null -w '%{http_code}' "https://$DOMAIN/")
    echo "  https://$DOMAIN/ -> $code"
    curl -fsS "https://$DOMAIN/manifest.json" | grep -q '"name": "Keep Focus"' && ok "Manifest do Keep Focus no ar" || echo "Aviso: o manifest ainda não é o do Keep Focus."
}

case "${1:-}" in
    backup) backup ;;
    nginx) nginx_site ;;
    env) env_file "${2:-}" ;;
    check) check ;;
    *) sed -n '2,10p' "$0"; exit 1 ;;
esac
