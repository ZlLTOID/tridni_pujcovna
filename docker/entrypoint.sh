#!/bin/bash
set -e

mkdir -p /var/www/html/public/uploads /var/www/html/var/cache /var/www/html/var/log
chown -R www-data:www-data /var/www/html/public/uploads /var/www/html/var
chmod -R 775 /var/www/html/public/uploads /var/www/html/var

if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "Instaluji Composer závislosti do volume..."
    composer install --no-interaction --optimize-autoloader
fi

echo "Čekám na MySQL..."
until php -r "
    \$url = getenv('DATABASE_URL') ?: 'mysql://pujcovna:pujcovna@mysql:3306/pujcovna';
    \$parts = parse_url(\$url);
    \$host = \$parts['host'] ?? 'mysql';
    \$port = \$parts['port'] ?? 3306;
    \$user = \$parts['user'] ?? 'pujcovna';
    \$pass = \$parts['pass'] ?? 'pujcovna';
    \$db = ltrim(\$parts['path'] ?? '/pujcovna', '/');
    new PDO(\"mysql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass);
" 2>/dev/null; do
    sleep 2
done

echo "Aktualizuji schéma databáze..."
php /var/www/html/bin/console doctrine:schema:update --force --no-interaction

echo "Inicializuji výchozí data..."
php /var/www/html/bin/console app:init-database --no-interaction

if [ "${APP_ENV:-dev}" = "prod" ]; then
    echo "Čistím cache (prod)..."
    php /var/www/html/bin/console cache:clear --no-interaction
    php /var/www/html/bin/console cache:warmup --no-interaction
else
    echo "Režim dev — cache se obnoví automaticky při změně."
fi

exec "$@"
