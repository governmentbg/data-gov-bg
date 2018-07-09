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
ldconfig -p | grep libpng 2>&1 || {
    sudo apt-get install libpng12-dev -y

    if [ -d node_modules ]
    then
        sudo rm -rf node_modules

        echo 'Folder node_modules deleted.'
    fi

    echo 'Dependencies installed.'
}

npm install

echo 'Deploy finished.'
