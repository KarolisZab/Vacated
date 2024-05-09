FROM php:8.2-apache
RUN apt-get update && apt-get install -y \
        git \
        postgresql-client \
        libpq-dev \
        libcurl4-openssl-dev \
        pkg-config \
        libssl-dev \
        locales \
        zip \
        apt-transport-https \
        python3-pip \
        libicu-dev \
        libapache2-mod-security2 \
        libxml2-dev \
    && docker-php-ext-install pdo_pgsql

RUN curl -fsSL https://deb.nodesource.com/setup_21.x | bash - \
    && apt-get update \
    && apt-get install -y nodejs

RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - \
    && echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list \
    && apt-get update \
    && apt-get install -y yarn

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer global config allow-plugins.symfony/flex true --no-interaction
RUN set -eux; \
    composer global require "symfony/flex" --prefer-dist --no-progress --no-suggest --classmap-authoritative; \
    composer clear-cache
ENV PATH="${PATH}:/root/.composer/vendor/bin"

RUN a2enmod rewrite headers
RUN a2enmod security2
ARG app_env='prod'
ARG app_debug='0'
ARG url_basepath='/'
ARG app_version=''
ENV APP_ENV=${app_env}

RUN sed -i 's@html@html/public@g' /etc/apache2/sites-available/000-default.conf
RUN sed -i "/<\/VirtualHost>/ i\Options FollowSymLinks" /etc/apache2/sites-available/000-default.conf
RUN echo "ServerTokens Full" >> /etc/apache2/conf-available/security.conf
RUN echo "ServerSignature Off" >> /etc/apache2/conf-available/security.conf
RUN echo 'SecServerSignature " "' >> /etc/apache2/conf-available/security.conf
RUN echo 'SecStatusEngine On' >> /etc/apache2/conf-available/security.conf

COPY ./composer.json /var/www/html
COPY ./composer.lock /var/www/html
COPY ./symfony.lock /var/www/html
COPY ./package.json /var/www/html
COPY ./yarn.lock /var/www/html
RUN cd /var/www/html && composer install --no-scripts --no-autoloader
COPY ./ /var/www/html
RUN yarn install
RUN yarn build

RUN mkdir /var/www/html/var || echo 'var directory already exists'
RUN chmod -R 775 /var/www/html/var

RUN composer dump-autoload --optimize
RUN chmod -R 777 /tmp
RUN php bin/console cache:warmup --no-optional-warmers
RUN chown www-data -R /var/www/html/var
