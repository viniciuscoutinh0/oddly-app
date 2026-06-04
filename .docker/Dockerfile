FROM alpine:3.22

ARG USER=developer
ARG UID=1000

RUN apk update && apk add --no-cache \
    curl ca-certificates zip unzip sqlite nodejs npm \
    php84 php84-fpm php84-curl php84-mbstring php84-bcmath \
    php84-openssl php84-pdo php84-pdo_sqlite php84-tokenizer \
    php84-fileinfo php84-zip php84-intl php84-xml php84-ctype \
    php84-phar php84-pcntl php84-session php84-dom php84-xmlreader \
    php84-xmlwriter php84-simplexml php84-iconv php84-gd \
    php84-exif php84-redis php84-opcache \
    && ln -s /usr/bin/php84 /usr/bin/php \
    && rm -rf /var/cache/apk/* /tmp/* /usr/share/man /usr/share/doc

RUN curl -sS https://getcomposer.org/installer | php \
    -- --install-dir=/usr/local/bin --filename=composer

RUN addgroup -g ${UID} -S ${USER} \
    && adduser -u ${UID} -S ${USER} -G ${USER} \
    && mkdir -p /var/log/ /var/run/ /var/www/ \
    && chown -R ${USER}:${USER} /var/log/ /var/run/ /var/www/ \
    && chmod -R 775 /var/log/ /var/run /var/www

COPY ./php/php-fpm.conf /etc/php84/php-fpm.conf
COPY ./php/php.ini /etc/php84/php.ini

WORKDIR /var/www/html
USER ${USER}

CMD ["php-fpm84", "--nodaemonize", "--fpm-config", "/etc/php84/php-fpm.conf"]
