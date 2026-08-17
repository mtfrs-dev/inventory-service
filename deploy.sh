#!/usr/bin/env bash
#
# Redeploys this app on VM02 following the per-port runbook's Fase 2 order:
# build -> up -> composer install -> chown 33:33 -> migrate -> re-cache.
# Run this ON the server, inside the cloned project directory
# (/opt/stack/apps/inventaris).
#
# Note: this only covers redeploys of an already-provisioned app. Initial
# setup (Fase 1: copying the container template, editing .env, Fase 3: adding
# the ports: block, Fase 4: ufw allow from .188) is a one-time manual step —
# see the runbook.
#
# Usage: ./deploy.sh [branch]
#   branch  Git branch to deploy (default: main)

set -euo pipefail

BRANCH="${1:-main}"
SERVICE="app"

log() { echo -e "\n==> $*"; }

if [ ! -f .env ]; then
    echo "ERROR: .env not found in $(pwd). Deploy aborted." >&2
    exit 1
fi

if [ -n "$(git status --porcelain)" ]; then
    echo "ERROR: uncommitted changes in $(pwd) — 'git reset --hard' would discard them." >&2
    echo "Commit, stash, or discard them before deploying. Deploy aborted." >&2
    git status --short >&2
    exit 1
fi

log "Fetching latest code (${BRANCH})"
git fetch origin "${BRANCH}"

UNPUSHED="$(git rev-list "origin/${BRANCH}..HEAD" 2>/dev/null || true)"
if [ -n "${UNPUSHED}" ]; then
    echo "ERROR: local ${BRANCH} has commits not on origin/${BRANCH} — 'git reset --hard' would discard them." >&2
    echo "Push them first (git push origin ${BRANCH}) before deploying. Deploy aborted." >&2
    git log --oneline "origin/${BRANCH}..HEAD" >&2
    exit 1
fi

git checkout "${BRANCH}"
git reset --hard "origin/${BRANCH}"

log "Building image"
docker compose build

log "Recreating containers"
docker compose up -d --remove-orphans

log "Waiting for ${SERVICE} to be ready"
until docker compose exec -T "${SERVICE}" php -v >/dev/null 2>&1; do
    sleep 2
done

log "Installing dependencies"
docker compose exec -T "${SERVICE}" composer install --no-dev --optimize-autoloader

log "Fixing storage/bootstrap ownership (UID 33 = www-data on Debian)"
docker compose exec -T "${SERVICE}" sh -lc 'chown -R 33:33 storage bootstrap/cache'

log "Running database migrations"
docker compose exec -T "${SERVICE}" php artisan migrate --force

log "Re-caching config/routes/views"
docker compose exec -T "${SERVICE}" php artisan config:cache
docker compose exec -T "${SERVICE}" php artisan route:cache
docker compose exec -T "${SERVICE}" php artisan view:cache

log "Pruning dangling images"
docker image prune -f >/dev/null

log "Deploy complete: app listening on port 8004"
