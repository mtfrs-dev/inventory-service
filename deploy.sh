#!/usr/bin/env bash
#
# Deploys the current server checkout using docker-compose.prod.yaml
# (self-contained, no base file needed). Run this ON the server, inside
# the project directory.
#
# Usage: ./deploy.sh [branch]
#   branch  Git branch to deploy (default: main)

set -euo pipefail

BRANCH="${1:-main}"
COMPOSE_FILES=(-f docker-compose.prod.yaml)

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

log "Building images"
docker compose "${COMPOSE_FILES[@]}" build

log "Recreating containers"
docker compose "${COMPOSE_FILES[@]}" up -d --remove-orphans

log "Waiting for app container to be ready"
until docker compose "${COMPOSE_FILES[@]}" exec -T app php artisan --version >/dev/null 2>&1; do
    sleep 2
done

log "Running database migrations"
docker compose "${COMPOSE_FILES[@]}" exec -T app php artisan migrate --force

log "Clearing/re-caching config (in case .env changed)"
docker compose "${COMPOSE_FILES[@]}" exec -T app php artisan config:cache
docker compose "${COMPOSE_FILES[@]}" exec -T app php artisan route:cache
docker compose "${COMPOSE_FILES[@]}" exec -T app php artisan view:cache

log "Pruning dangling images"
docker image prune -f >/dev/null

log "Deploy complete: app listening on port 8004"
