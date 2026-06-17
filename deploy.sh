#!/usr/bin/env bash
#
# Production deploy for Amal Al Raisi (run from the app's public_html on the server).
# Fixes the "old assets keep showing" problem: it rebuilds the hashed front-end
# bundle, runs migrations, and refreshes every Laravel cache so new code, views and
# the Vite manifest take effect immediately. Static images/video are cache-busted in
# code via asset_version() (?v=mtime) and Spatie media URLs via version_urls=true.
#
set -euo pipefail

echo "→ Pulling latest code"
git pull --ff-only

echo "→ Installing PHP dependencies"
composer install --no-interaction --prefer-dist --optimize-autoloader

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

echo "✓ Deploy complete."
echo
echo "If this app uses Cloudways Varnish (full-page cache), purge it so cached HTML"
echo "refreshes:  Cloudways panel → your app → Application Settings → Purge Varnish."
