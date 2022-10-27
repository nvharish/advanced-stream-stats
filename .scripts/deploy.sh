
echo "Deployment Started..."
cd ./public_html/api/v1
git pull origin main
cp .env.example .env
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
php artisan cache:clear
php artisan migrate --force
echo "Deployment Finished!!!"