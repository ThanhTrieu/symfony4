FROM php:7.2.10-fpm

#COPY php.ini /usr/local/etc/php/php.ini
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
# Install common lib, libmcrypt-dev need for install "mcrypt" ext
RUN apt-get update \
    && apt-get install -y nano unzip curl htop libmcrypt-dev

RUN apt-get install -y net-tools libmemcached-dev zlib1g-dev libpng-dev

RUN docker-php-ext-install pdo pdo_mysql calendar zip gd

RUN pecl install memcached-3.0.4 \
	&& docker-php-ext-enable memcached

RUN pecl install redis-4.1.1 \
	&& pecl install xdebug-2.6.0 \
	&& docker-php-ext-enable redis xdebug

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
COPY ./conf/php*.conf /usr/local/etc/php-fpm.d/

WORKDIR /opt/www

#RUN mkdir -p /opt/www/dvg-web/public_html
#RUN mkdir -p /opt/www/dvg-web/logs