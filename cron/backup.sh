#!/usr/bin/env bash
# 0 2 * * *  /path/coop/cron/backup.sh
# Daily mysqldump, keep 30 days. Reads DB_* from ../.env. Uses a temporary option file so the password is not on the command line.
set -euo pipefail
DIR="$(cd "$(dirname "$0")/.." && pwd)"
set -a; [ -f "$DIR/.env" ] && . "$DIR/.env"; set +a
OUT="$DIR/storage/backups"
mkdir -p "$OUT"
CNF="$(mktemp)"; trap 'rm -f "$CNF"' EXIT
printf '[client]\nuser=%s\npassword=%s\nhost=%s\nport=%s\n' "${DB_BACKUP_USER:-${DB_USER:-root}}" "${DB_BACKUP_PASS:-${DB_PASS:-}}" "${DB_HOST:-127.0.0.1}" "${DB_PORT:-3306}" > "$CNF"
mysqldump --defaults-extra-file="$CNF" --single-transaction --routines --triggers --events "${DB_NAME:-coop_db}" | gzip > "$OUT/coop_db-$(date +%F).sql.gz"
find "$OUT" -name 'coop_db-*.sql.gz' -mtime +30 -delete
echo "Backup written to $OUT"
