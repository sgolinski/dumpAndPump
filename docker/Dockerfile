FROM php:8.1.9-cli-alpine

WORKDIR /mnt/app

ENV PANTHER_NO_SANDBOX 1
ENV PANTHER_CHROME_ARGUMENTS='--disable-dev-shm-usage'


ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS="0" \
    PHP_OPCACHE_MAX_ACCELERATED_FILES="10000" \
    PHP_OPCACHE_MEMORY_CONSUMPTION="192" \
    PHP_OPCACHE_MAX_WASTED_PERCENTAGE="10"

COPY /opcache.ini /usr/local/etc/php/conf.d/

RUN apk add libzip-dev libzip zip libxml2-dev \
    && docker-php-ext-install zip xml opcache

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions sockets gd


RUN apk add --no-cache chromium chromium-chromedriver
ARG GECKODRIVER_VERSION=0.30.0
RUN wget -q https://github.com/mozilla/geckodriver/releases/download/v$GECKODRIVER_VERSION/geckodriver-v$GECKODRIVER_VERSION-linux64.tar.gz; \
    tar -zxf geckodriver-v$GECKODRIVER_VERSION-linux64.tar.gz -C /usr/bin; \
    rm geckodriver-v$GECKODRIVER_VERSION-linux64.tar.gz

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"\
   && php composer-setup.php --install-dir=$(pwd) --filename=composer\
   && php -r "unlink('composer-setup.php');"

ADD /src src
ADD /tests tests
ADD /public public
#ADD /index.php index.php
ADD /composer.json composer.json
ADD /composer.lock composer.lock

RUN php composer install

COPY /crontab /etc/cron.d/crawler-job

# give the new cronfile execution rights
RUN chmod 0644 /etc/cron.d/crawler-job \
    && crontab /etc/cron.d/crawler-job

# run cron daemon in foreground
CMD crond -f
