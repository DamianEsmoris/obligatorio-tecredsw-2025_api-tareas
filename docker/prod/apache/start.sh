#!/bin/sh

if [ -n "${REPO_URL}" ]; then
    pushd $(pwd)
    cd /var/www/html
    git clone --depth=1 ${REPO_URL} /var/www/html
    for f in {.*,*}; do [[ ${f} = '.git' || ${f} = 'public' ]] || rm -rf $f; done
    popd
fi

sed "s|\${PHP_FPM_URL}|${PHP_FPM_URL}|g" /default.conf.template > /etc/httpd/conf.d/default.conf
httpd -D FOREGROUND
