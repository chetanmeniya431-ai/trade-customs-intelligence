#!/bin/bash

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views \
         storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "Waiting for Postgres at ${DB_HOST:-db}:${DB_PORT:-5432}..."
until php -r "
try {
    new PDO('pgsql:host=${DB_HOST:-db};port=${DB_PORT:-5432};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');
    exit(0);
} catch (Exception \$e) {
    exit(1);
}
"; do
  sleep 1
done
echo "Postgres is up."

php artisan storage:link --force || true

if ! grep -qE '^APP_KEY=base64:' .env 2>/dev/null; then
    php artisan key:generate --ansi --force
fi

php artisan config:clear
php artisan migrate --force

# Seed only when the database is empty — skip on re-deploys to avoid
# unique-constraint errors from seeders that use plain create().
USER_COUNT=$(php -r "
try {
    \$pdo = new PDO('pgsql:host=${DB_HOST:-db};port=${DB_PORT:-5432};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');
    echo \$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
} catch (Exception \$e) { echo '0'; }
" 2>/dev/null)
if [ "${USER_COUNT:-0}" -eq "0" ]; then
    echo "Fresh database — running seeders..."
    php artisan db:seed --force || echo "Seed warning: some records may already exist. Continuing."
else
    echo "Database already has data (${USER_COUNT} users) — skipping seed."
fi

exec "$@"
