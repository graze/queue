FROM php:5.6-cli

RUN pecl install xdebug && \
    echo "zend_extension=xdebug.so" >> "/usr/local/etc/php/conf.d/xdebug.ini"

RUN ["docker-php-ext-install", "mbstring"]

ADD . /opt/graze/queue

WORKDIR /opt/graze/queue
