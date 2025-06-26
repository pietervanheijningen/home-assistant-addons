ARG BUILD_FROM
FROM $BUILD_FROM

WORKDIR /opt/epever
# Enable edge repos...
RUN sed -i '/edge/s/^#//' /etc/apk/repositories

RUN apk add --no-cache \
    make \
    g++ \
    gcc \
    bash \
    php \
    composer

ADD src/composer.json composer.json

RUN composer install

ADD src/ .

HEALTHCHECK CMD pgrep php; if [ 0 != $? ]; then exit 1; fi;

CMD ["/bin/bash", "/opt/epever/entrypoint.sh"]
