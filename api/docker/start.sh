#!/bin/sh
set -e

echo "==> TontiTOGO API — Démarrage sur Render"

cd /var/www/html

# Créer et fixer les permissions sur tous les dossiers storage nécessaires
mkdir -p storage/logs \
         storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/api-docs \
         bootstrap/cache

chmod -R 777 storage bootstrap/cache

# 1. Vider les caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 3. Optimiser pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Vérifier APP_KEY
if [ -z "$APP_KEY" ]; then
  echo "⚠️  APP_KEY manquante — génération automatique"
  php artisan key:generate --force
fi

# 5. Migrations
echo "==> Migrations Supabase..."
php artisan migrate --force

# 6. Générer la documentation Swagger (une seule fois au démarrage)
echo "==> Génération Swagger..."
php artisan l5-swagger:generate 2>/dev/null || echo "Swagger: skip (pas d'annotations ou erreur)"

# 7. Démarrer Nginx + PHP-FPM
echo "==> Démarrage Nginx + PHP-FPM"
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
