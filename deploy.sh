#!/bin/bash

cd "$(dirname ${BASH_SOURCE[0]})"

git fetch --tags
git checkout $1

if ! [[ $1 =~ ^v?[0-9\.]+$ ]]
then
   git merge origin/$1
fi

# Install new composer packages
/usr/local/bin/composer install --prefer-dist --no-interaction

# Cache boost configuration and routes
php artisan cache:clear
php artisan config:clear
php artisan config:cache
php artisan route:cache

# Sync database changes
php artisan migrate

# Restart workers
php artisan queue:restart

# Install new node modules
npm install

# Build assets when using Laravel Mix
npm run production

echo 'Deploy finished.'
