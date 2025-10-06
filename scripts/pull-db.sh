#!/usr/bin/env bash
set -euo pipefail

source "$(dirname "$0")/.server.env"

ENVSEL="${1:-staging}"
case "$ENVSEL" in
  staging)
    DB_HOST="$STG_DB_HOST"; DB_NAME="$STG_DB_NAME"; DB_USER="$STG_DB_USER"; DB_PASS="$STG_DB_PASS"
    ;;
  production)
    DB_HOST="$PRD_DB_HOST"; DB_NAME="$PRD_DB_NAME"; DB_USER="$PRD_DB_USER"; DB_PASS="$PRD_DB_PASS"
    ;;
  *)
    echo "Usage: $0 staging|production" ; exit 1 ;;
esac

TS=$(date +%Y%m%d_%H%M%S)
OUT="backups/${ENVSEL}_${TS}.sql.gz"
echo "→ Dumping ${ENVSEL} (${DB_NAME}) from ${CP_HOST} → ${OUT}"

# Use only flags that work on MySQL 5.x / MariaDB, and quiet the locale/tput noise.
# Also avoid the "password on CLI" warning by using MYSQL_PWD on the remote.
ssh -p "${SSH_PORT:-22}" "${CP_USER}@${CP_HOST}" "
  export LANG=C LC_ALL=C TERM=dumb;
  MYSQL_PWD='${DB_PASS}' mysqldump \
    -h '${DB_HOST}' -u '${DB_USER}' \
    --single-transaction --quick --routines --triggers --events \
    --default-character-set=utf8mb4 \
    '${DB_NAME}' | gzip -c
" > "${OUT}"

echo "✅ Saved ${OUT}"
