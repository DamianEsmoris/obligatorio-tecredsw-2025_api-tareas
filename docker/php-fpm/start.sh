#!/bin/bash

[ -z "${SKIP_COMPOSER}" ] \
    && composer install

[ -z "${SKIP_MIGRATIONS}" ] \
    && php artisan migrate

[ -z "${SKIP_SEEDERS}" ] \
    && php artisan db:seed

[ -z "${STORAGE_LINK}" ] \
    && php artisan storage:link

if [ ! -f .env ]
then
   cp .env.example .env
   php artisan key:generate
fi

php-fpm -F -y /etc/php-fpm.conf
