#!/usr/bin/env bash
set -e

if [[ $# -ne 1 ]]; then
    echo "Usage: $0 <server-username>"
    exit 1
fi

if [[ $EUID -ne 0 ]]; then
    echo "This script must be run as root"
    exit 1
fi

php artisan down

sudo -u $1 git pull

rm -f vendor/compiled.php

sudo -u $1 composer update

sudo -u $1 npm update

sudo -u $1 node_modules/.bin/gulp --production

sudo -u $1 php artisan migrate

sudo -u $1 php artisan optimize

php artisan up
