#!/usr/bin/env bash
# Validates the legacy supplements copy (etapa 5) against a copy of the production database.
# Runs on the development machine; the production server is only read (scp).
#
# Usage: scripts/validate-production-db.sh [user@host]
#   With a host, downloads the database first into database/producao.sqlite.
set -euo pipefail

cd "$(dirname "$0")/.."

REMOTE_PATH=/opt/projects/Keep-Training-app/database/database.sqlite
LOCAL_COPY=database/producao.sqlite

if [ $# -ge 1 ]; then
    echo "▶ Baixando o banco de produção de $1..."
    scp "$1:$REMOTE_PATH" "$LOCAL_COPY"
fi

if [ ! -f "$LOCAL_COPY" ]; then
    echo "✖ $LOCAL_COPY não existe. Rode com o host (ex.: root@187.77.60.230) ou copie o arquivo antes." >&2
    exit 1
fi

echo "▶ Rodando as migrations numa cópia temporária (o arquivo $LOCAL_COPY não é alterado)..."
./vendor/bin/sail exec -T -u sail laravel bash -s <<'INNER'
set -euo pipefail
TMP=/tmp/kf-prod-validation.sqlite
cp database/producao.sqlite "$TMP"
trap 'rm -f "$TMP"' EXIT
export DB_DATABASE="$TMP" LOG_CHANNEL=stderr

before=$(php artisan tinker --execute='echo DB::table("suplementos")->count()." ".DB::table("consumo_diario")->count();')
php artisan migrate --force --no-interaction 2>&1 | grep -E "DONE|FAIL|Nothing" || true

php artisan tinker --execute='
$legacy = [DB::table("suplementos")->count(), DB::table("consumo_diario")->count()];
$new = [DB::table("supplements")->count(), DB::table("supplement_logs")->count()];
$foreign = DB::table("consumo_diario as c")->join("suplementos as s", "s.id", "=", "c.suplemento_id")->whereColumn("c.user_id", "!=", "s.user_id")->count();
$rules = DB::table("suplementos")->whereNotIn("regra_uso", ["diario", "apenas_treino"])->count();
printf("\nSuplementos:  %d antigos -> %d novos%s\n", $legacy[0], $new[0], $legacy[0] === $new[0] ? "  ✔" : "  ✖");
printf("Consumos:     %d antigos -> %d novos (%d de outro usuário ignorados)%s\n", $legacy[1], $new[1], $foreign, $legacy[1] - $foreign === $new[1] ? "  ✔" : "  ✖");
printf("Regras desconhecidas convertidas para daily: %d\n", $rules);
echo "\nPor usuário (suplementos antigos -> novos):\n";
foreach (DB::table("users")->orderBy("id")->get(["id", "email"]) as $u) {
    $a = DB::table("suplementos")->where("user_id", $u->id)->count();
    $b = DB::table("supplements")->where("user_id", $u->id)->count();
    if ($a || $b) { printf("  #%d %s: %d -> %d%s\n", $u->id, $u->email, $a, $b, $a === $b ? "" : "  ✖"); }
}
'
echo "(antes da cópia: suplementos e consumos = $before)"
INNER
