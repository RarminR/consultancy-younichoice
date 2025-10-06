#!/usr/bin/env bash
set -euo pipefail
ENV="${1:-}"; [[ "$ENV" =~ ^(staging|production)$ ]] || { echo "Usage: $0 staging|production"; exit 1; }
ROOT="deploy/$ENV"; TS=$(date +%Y%m%d%H%M%S); DEST="$ROOT/releases/$TS"
APP="app-stg"; [[ "$ENV" == "production" ]] && APP="app-prod"
mkdir -p "$DEST"
rsync -a --delete --exclude ".git" --exclude ".github" --exclude "deploy" \
  --exclude "_data" --exclude "node_modules" --exclude "storage" --exclude ".env*" ./ "$DEST/"
ln -sfn ../../shared/.env "$DEST/.env"
ln -sfn ../../shared/storage "$DEST/storage"
docker compose --profile "$ENV" up -d "$APP" >/dev/null
docker compose run --rm "$APP" bash -lc "cd /deploy/releases/$TS && \
  (composer install --no-dev --prefer-dist --no-interaction || \
   (curl -sS https://getcomposer.org/installer | php && php composer.phar install --no-dev --prefer-dist --no-interaction))"
if [[ -f "phinx.php" ]]; then
  docker compose run --rm "$APP" bash -lc "cd /deploy/releases/$TS && php vendor/bin/phinx migrate -e $ENV && php vendor/bin/phinx seed:run -e $ENV || true"
fi
ln -sfn "releases/$TS" "$ROOT/current"
docker compose restart "$APP" >/dev/null
echo "✅ Deployed $ENV → $DEST"
