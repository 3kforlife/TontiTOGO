#!/bin/sh
set -e

echo "==> TontiTOGO API — Démarrage sur Render"

cd /var/www/html

# 1. Vider le cache de config (variables d'env Render injectées)
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 2. Optimiser pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 3. Générer la clé si absente (rare mais possible au 1er démarrage)
if [ -z "$APP_KEY" ]; then
  echo "⚠️  APP_KEY manquante — génération automatique"
  php artisan key:generate --force
fi

# 4. Exécuter les migrations (--force pour prod)
echo "==> Migrations..."
php artisan migrate --force

# 5. Lancer Nginx + PHP-FPM via Supervisor
echo "==> Démarrage Nginx + PHP-FPM"
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
