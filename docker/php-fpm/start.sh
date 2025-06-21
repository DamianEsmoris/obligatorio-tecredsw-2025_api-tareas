#!/bin/bash

composer install
php artisan key:generate
php artisan migrate:fresh
php artisan db:seed

php-fpm -F -y /etc/php-fpm.conf
