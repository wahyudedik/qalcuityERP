#!/bin/bash
# ═══════════════════════════════════════════════════════════════
#  Qalcuity ERP — Production Deployment Script (Ubuntu)
#  Jalankan via SSH setiap kali ada update code ke production
#  Path: /www/wwwroot/qalcuity.com

# cd /www/wwwroot/qalcuity.com
# git checkout -- deploy.sh
# git pull origin main
# chmod +x deploy.sh
# ./deploy.sh

# ═══════════════════════════════════════════════════════════════

set -e

# Supaya Composer tidak warning soal root
export COMPOSER_ALLOW_SUPERUSER=1

echo ""
echo "============================================================"
echo "  QALCUITY ERP - PRODUCTION DEPLOYMENT"
echo "============================================================"
echo ""

# ── 1. Aktifkan Maintenance Mode ─────────────────────────────
echo "[1/10] Mengaktifkan maintenance mode..."
php artisan down --retry=60 --refresh=15 || true
echo ""

# ── 2. Pull latest code dari Git ─────────────────────────────
echo "[2/10] Pull latest code dari repository..."
git stash --include-untracked 2>/dev/null || true
git fetch origin main
git reset --hard origin/main
git clean -fd 2>/dev/null || true
echo ""

# ── 3. Install/update Composer dependencies ──────────────────
echo "[3/10] Install Composer dependencies (production)..."
composer install --no-dev --optimize-autoloader --no-interaction
echo ""

# ── 4. Install NPM dependencies & build assets ──────────────
echo "[4/10] Install NPM dependencies (termasuk devDependencies untuk build)..."
npm ci
echo ""

echo "[5/10] Build frontend assets..."
npx vite build || npm run build:memory
echo ""

# Hapus devDependencies setelah build selesai
echo "    Membersihkan devDependencies..."
npm prune --omit=dev
echo ""

# ── 5. Jalankan database migrations ─────────────────────────
echo "[6/10] Menjalankan database migrations..."
php artisan migrate --force
echo ""

# ── 6. Clear & rebuild semua cache ──────────────────────────
echo "[7/10] Membersihkan cache lama..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear
echo ""

echo "[8/10] Rebuild cache untuk production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
echo ""

# ── 7. Restart queue workers ────────────────────────────────
echo "[9/10] Restart queue workers..."
php artisan queue:restart
echo ""

# ── 8. Nonaktifkan Maintenance Mode ─────────────────────────
echo "[10/10] Menonaktifkan maintenance mode..."
php artisan up
echo ""

echo "============================================================"
echo "  DEPLOYMENT SELESAI!"
echo "============================================================"
echo ""
echo "  Checklist pasca-deploy:"
echo "  - Pastikan queue worker berjalan: sudo supervisorctl status"
echo "  - Pastikan scheduler cron aktif: crontab -l"
echo "  - Cek log: tail -f storage/logs/laravel.log"
echo ""
