FROM rockylinux:9

RUN dnf update -y \
    && dnf install -y https://rpms.remirepo.net/enterprise/remi-release-9.rpm

RUN dnf module enable -y php:remi-8.2

RUN dnf install -y php82 php82-php-fpm php82-php-cli \
    && dnf clean all \
    && rm -rf /var/cache/dnf

ENV PATH="/opt/remi/php82/root/usr/bin:${PATH}"

RUN curl https://getcomposer.org/installer -o composer-installer.php \
    && php composer-installer.php --filename=composer --install-dir=/usr/local/bin \
    && rm composer-installer.php

RUN cat <<EOF > /etc/php82-fpm.conf
include=/etc/php-fpm.d/*.conf
[global]
pid =  0
error_log = /proc/self/fd/2
log_level = notice
log_buffering = no
daemonize = no
EOF

RUN mkdir -p /etc/php-fpm.d/

RUN cat <<EOF > /etc/php-fpm.d/www.conf
[www]
access.log = /proc/self/fd/2
catch_workers_output = yes
user = apache
group = apache
listen = 0.0.0.0:9000
listen.acl_users = apache,nginx
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
php_admin_value[error_log] = /var/log/php-fpm/www-error.log
php_admin_flag[log_errors] = on
php_value[session.save_handler] = files
php_value[session.save_path]    = /var/lib/php/session
EOF

WORKDIR /var/www/html/

COPY --chmod=500 start.sh /start.sh

CMD /start.sh
