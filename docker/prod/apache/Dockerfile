FROM rockylinux:9

RUN dnf install httpd git -y && \
    dnf clean all && \
    rm -rf /var/cache/dnf

RUN echo "" > /etc/httpd/conf.d/welcome.conf && \
    echo "" > /etc/httpd/conf.d/userdir.conf && \
    echo "" > /etc/httpd/conf.d/autoindex.conf

COPY default.conf.template /default.conf.template
COPY --chmod=0544 start.sh /start.sh

ENTRYPOINT ["/start.sh"]
