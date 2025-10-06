#!/usr/bin/env bash
set -euo pipefail
for ENV in staging production; do
  ROOT="deploy/$ENV"
  mkdir -p "$ROOT/shared/storage" "$ROOT/releases"
  [[ -f ".env.$ENV" ]] && cp -n ".env.$ENV" "$ROOT/shared/.env" || true
  TS=$(date +%Y%m%d%H%M%S); DEST="$ROOT/releases/$TS"; mkdir -p "$DEST"
  rsync -a --delete --exclude ".git" --exclude ".github" --exclude "deploy" \
    --exclude "_data" --exclude "node_modules" --exclude "storage" --exclude ".env*" ./ "$DEST/"
  ln -sfn ../../shared/.env "$DEST/.env"
  ln -sfn ../../shared/storage "$DEST/storage"
  ln -sfn "releases/$TS" "$ROOT/current"
done
echo "Bootstrap done."
