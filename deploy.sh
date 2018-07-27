#!/bin/bash

cd "$(dirname ${BASH_SOURCE[0]})"

git fetch --tags
git checkout $1

if ! [[ $1 =~ ^v?[0-9\.]+$ ]]
then
   git merge origin/$1
fi

# Install new composer packages
/usr/local/bin/composer install --no-interaction

# Cache boost configuration and routes
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Sync database changes
php artisan migrate

# Restart workers
php artisan queue:restart

echo 'Deploy finished.'
