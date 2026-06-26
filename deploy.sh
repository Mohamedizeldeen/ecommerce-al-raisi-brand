#!/usr/bin/env bash
#
# Production deploy for Amal Al Raisi (run from the app's public_html on the server).
# Fixes the "old assets keep showing" problem: it rebuilds the hashed front-end
# bundle, runs migrations, and refreshes every Laravel cache so new code, views and
# the Vite manifest take effect immediately. Static images/video are cache-busted in
# code via asset_version() (?v=mtime) and Spatie media URLs via version_urls=true.
#
set -euo pipefail

echo "→ Verifying production configuration"
# Refuse to deploy with debug/local settings — that would leak stack traces (and
# disable the CSP, which is skipped on the 'local' environment) on a live store.
if grep -qiE '^APP_ENV=local' .env 2>/dev/null; then
  echo "✗ Refusing to deploy: APP_ENV=local in .env (set APP_ENV=production)."; exit 1
fi
if grep -qiE '^APP_DEBUG=true' .env 2>/dev/null; then
  echo "✗ Refusing to deploy: APP_DEBUG=true in .env (set APP_DEBUG=false)."; exit 1
fi

echo "→ Pulling latest code"
git pull --ff-only

echo "→ Installing PHP dependencies"
composer install --no-interaction --prefer-dist --optimize-autoloader

echo "→ Backing up the database before migrating (best-effort)"
# A pre-migration dump so a bad migration is recoverable. Non-fatal: if mysqldump
# isn't available the deploy still proceeds (consider managed DB snapshots too).
if command -v mysqldump >/dev/null 2>&1; then
  DB_DATABASE=$(grep -E '^DB_DATABASE=' .env | head -1 | cut -d= -f2- | tr -d '"' || true)
  DB_USERNAME=$(grep -E '^DB_USERNAME=' .env | head -1 | cut -d= -f2- | tr -d '"' || true)
  DB_PASSWORD=$(grep -E '^DB_PASSWORD=' .env | head -1 | cut -d= -f2- | tr -d '"' || true)
  DB_HOST=$(grep -E '^DB_HOST=' .env | head -1 | cut -d= -f2- | tr -d '"' || true)
  if [ -n "${DB_DATABASE:-}" ]; then
    mkdir -p storage/backups
    BACKUP="storage/backups/pre-migrate-$(date +%Y%m%d-%H%M%S).sql"
    if MYSQL_PWD="${DB_PASSWORD:-}" mysqldump -h "${DB_HOST:-127.0.0.1}" -u "${DB_USERNAME:-root}" "$DB_DATABASE" > "$BACKUP" 2>/dev/null; then
      echo "  saved $BACKUP"
    else
      echo "  ⚠ backup skipped (mysqldump failed — check credentials)"
    fi
  fi
else
  echo "  ⚠ mysqldump not found — skipping pre-migration backup"
fi

echo "→ Running database migrations"
php artisan migrate --force

echo "→ Building front-end assets (hashed → auto cache-busted)"
npm ci
npm run build

echo "→ Linking storage (idempotent)"
php artisan storage:link >/dev/null 2>&1 || true

echo "→ Refreshing caches so the new release takes effect"
php artisan optimize:clear   # clear stale config/route/view/event caches
php artisan optimize         # re-cache for production speed

echo "→ Restarting queue workers so they pick up the new code"
# Signals any supervised worker to restart gracefully. Harmless if none is running,
# but a worker MUST be provisioned (see reminder below) or emails never send.
php artisan queue:restart >/dev/null 2>&1 || true

echo "✓ Deploy complete."
echo
echo "If this app uses Cloudways Varnish (full-page cache), purge it so cached HTML"
echo "refreshes:  Cloudways panel → your app → Application Settings → Purge Varnish."
echo
echo "Provision ONCE on the server (this script can't — they are system services):"
echo "  • A supervised queue worker (e.g. Supervisor) running:"
echo "      php artisan queue:work --tries=3 --backoff=10 --max-time=3600"
echo "    Without it, order-confirmation emails queue forever and never send."
echo "  • A cron entry running the scheduler every minute:"
echo "      * * * * * cd $(pwd) && php artisan schedule:run >> /dev/null 2>&1"
echo "    Without it, stale-order expiry and guest-cart pruning never run."
