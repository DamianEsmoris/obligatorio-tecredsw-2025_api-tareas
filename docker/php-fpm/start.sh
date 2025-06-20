#!/bin/bash

composer install
php artisan key:generate
php artisan migrate:fresh

/opt/remi/php82/root/usr/sbin/php-fpm -F -y /etc/php82-fpm.conf
php artisan serve
