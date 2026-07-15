#!/bin/sh
set -e

echo "==> TontiTOGO API — Démarrage sur Render"

cd /var/www/html

# Activer l'affichage des erreurs PHP pour diagnostiquer
php -r "echo 'PHP OK: ' . PHP_VERSION . PHP_EOL;"
php artisan --version || echo "ERREUR: artisan ne fonctionne pas"

# Créer et fixer les permissions sur tous les dossiers storage nécessaires
mkdir -p storage/logs \
         storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/api-docs \
         bootstrap/cache

chmod -R 777 storage bootstrap/cache

# 1. Vider uniquement le cache config (pas cache:clear qui écrit sur disque)
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 2. Optimiser pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Vérifier APP_KEY
if [ -z "$APP_KEY" ]; then
  echo "⚠️  APP_KEY manquante — génération automatique"
  php artisan key:generate --force
fi

# 4. Migrations
echo "==> Migrations Supabase..."
php artisan migrate --force

# 5. Démarrer Nginx + PHP-FPM
echo "==> Démarrage Nginx + PHP-FPM"
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
