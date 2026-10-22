#!/usr/bin/env bash
# Migrates the production database safely. Called by the deploy (pipeline.yml) on the VPS,
# from the project folder, after the new code is in place:
#   1. backs up database/database.sqlite into database/backups (keeps the last 10);
#   2. rehearses the migrations on a copy and stops the deploy if they fail;
#   3. only then migrates the real database.
set -euo pipefail

cd "$(dirname "$0")/.."

DB=database/database.sqlite
COPY=database/preflight.sqlite
SAIL=./vendor/bin/sail
# Path of the project inside the Sail container.
CONTAINER_DIR=/var/www/html

step() { echo "▶ $*"; }

if [ ! -f "$DB" ]; then
    step "Sem $DB; rodando as migrations direto."
    $SAIL artisan migrate --force
    exit 0
fi

step "Backup do banco"
mkdir -p database/backups
cp "$DB" "database/backups/database-$(date +%Y%m%d-%H%M%S).sqlite"
ls -1t database/backups/*.sqlite | tail -n +11 | xargs -r rm --

# A cached config ignores DB_DATABASE and would point the rehearsal at the real database.
step "Limpando o cache de configuração"
$SAIL artisan config:clear

step "Ensaio das migrations numa cópia"
cp "$DB" "$COPY"
trap 'rm -f "$COPY"' EXIT
if ! $SAIL exec -T -u sail laravel env DB_DATABASE="$CONTAINER_DIR/$COPY" php artisan migrate --force; then
    echo "✖ As migrations falharam na cópia. O banco real não foi alterado; o deploy parou aqui." >&2
    exit 1
fi

step "Migrations no banco real"
$SAIL artisan migrate --force
