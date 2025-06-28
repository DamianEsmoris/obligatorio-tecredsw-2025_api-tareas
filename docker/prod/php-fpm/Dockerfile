FROM rockylinux:9

RUN dnf update -y \
    && dnf install -y https://dl.fedoraproject.org/pub/epel/epel-release-latest-9.noarch.rpm \
    && dnf install -y https://rpms.remirepo.net/enterprise/remi-release-9.rpm

RUN curl -sSL -O https://packages.microsoft.com/config/rhel/9/packages-microsoft-prod.rpm \
    && dnf install -y packages-microsoft-prod.rpm \
    && rm packages-microsoft-prod.rpm

ENV ACCEPT_EULA=Y

RUN dnf install -y yum-utils \
    && dnf module -y reset php \
    && dnf module -y install php:remi-8.2

RUN dnf install -y git unzip php php-fpm php-cli php-pear php-devel php-sqlsrv msodbcsql18 \
    && dnf clean all \
    && rm -rf /var/cache/dnf || true

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

RUN mkdir -p /etc/php-fpm.d/ /run/php-fpm/

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
