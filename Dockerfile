#syntax=docker/dockerfile:1.4

# The different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target

# Prod image
FROM php:8.2-fpm-alpine AS app_php

# Allow to use development versions of Symfony
ARG STABILITY="stable"
ENV STABILITY ${STABILITY}

# Allow to select Symfony version
ARG SYMFONY_VERSION=6
ENV SYMFONY_VERSION ${SYMFONY_VERSION}

ENV APP_ENV=prod

WORKDIR /srv/app

# php extensions installer: https://github.com/mlocati/docker-php-extension-installer
COPY --from=mlocati/php-extension-installer --link /usr/bin/install-php-extensions /usr/local/bin/

RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN docker-php-ext-enable mysqli

# persistent / runtime deps
RUN apk update \
    && apk add --no-cache \
    	acl \
		fcgi \
		file \
		gettext \
		git \
    	tini \
    	openssh-client \
    	bash \
    && apk add --no-cache --virtual \
        devs \
        tar \
        curl \
    	nss-tools  \
    && rm -rf /var/cache/apk/* ;

RUN set -eux; \
    install-php-extensions \
    	intl \
    	bcmath \
    	mbstring \
    	zip \
    	apcu \
		opcache \
    ;

###> recipes ###
###< recipes ###

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY --link docker/php/conf.d/app.ini $PHP_INI_DIR/conf.d/
COPY --link docker/php/conf.d/app.prod.ini $PHP_INI_DIR/conf.d/

COPY --link docker/php/php-fpm.d/www-php.conf /usr/local/etc/php-fpm.d/www-php.conf
RUN mkdir -p /var/run/php

COPY --link docker/php/docker-healthcheck.sh /usr/local/bin/docker-healthcheck
RUN chmod +x /usr/local/bin/docker-healthcheck

HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD ["docker-healthcheck"]

COPY --link docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

RUN curl -sS https://get.symfony.com/cli/installer | php -- --install-dir=/usr/local/bin --filename=symfony

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/root/.composer/vendor/bin"

COPY --from=composer/composer:2-bin --link /composer /usr/bin/composer

# prevent the reinstallation of vendors at every changes in the source code
COPY composer.* symfony.* ./
RUN set -ux; \
    if [ -f composer.json ]; then \
		composer install --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress; \
		composer clear-cache; \
    fi

# copy sources
COPY --link  . .
RUN rm -Rf docker/

RUN set -ux; \
	mkdir -p var/cache var/log; \
    if [ -f composer.json ]; then \
		composer dump-autoload --classmap-authoritative --no-dev; \
		composer dump-env prod; \
		composer run-script --no-dev post-install-cmd; \
		chmod +x bin/console; sync; \
    fi

CMD composer i -o ; wait-for-it db:5432 -- bin/console doctrine:migrations:migrate ;  php-fpm

RUN rm -f .env.local.php

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]


# Dev image
FROM app_php AS app_php_dev

ENV APP_ENV=dev XDEBUG_MODE=off
VOLUME /srv/app/var/

RUN rm $PHP_INI_DIR/conf.d/app.prod.ini; \
	mv "$PHP_INI_DIR/php.ini" "$PHP_INI_DIR/php.ini-production"; \
	mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY --link docker/php/conf.d/app.dev.ini $PHP_INI_DIR/conf.d/

RUN set -eux; \
	install-php-extensions xdebug

ENV APP_ENV=dev XDEBUG_MODE=on

CMD composer i -o ; wait-for-it db:5432 -- bin/console doctrine:migrations:migrate ;  php-fpm

RUN rm -f .env.prod.php

EXPOSE 9000

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]


## Build Caddy with the Mercure and Vulcain modules
#FROM caddy:2.6-builder-alpine AS app_caddy_builder
#
#RUN xcaddy build \
#	--with github.com/dunglas/mercure \
#	--with github.com/dunglas/mercure/caddy \
#	--with github.com/dunglas/vulcain \
#	--with github.com/dunglas/vulcain/caddy


# Caddy image
#FROM caddy:2.6-alpine AS app_caddy
#
#WORKDIR /srv/app
#
#COPY --from=app_caddy_builder --link /usr/bin/caddy /usr/bin/caddy
#COPY --from=app_php --link /srv/app/public public/
#COPY --link docker/caddy/Caddyfile /etc/caddy/Caddyfile
#
#RUN apk update &&  \
#    apk --no-cache add tini \
#    	git \
#    	openssh-client \
#    	bash \
#    && apk --no-cache add --virtual \
#    	devs  \
#    	tar  \
#    	curl \
#    	nss-tools  \
#    && rm -rf /var/cache/apk/* ;

#RUN caddy trust

#FROM nginx:alpine
#COPY nginx_dev.conf  /etc/nginx/
#COPY nginx_prod.conf  /etc/nginx/
#COPY templates /etc/nginx/templates/
#WORKDIR /srv/app
## RUN echo "upstream php-upstream { server php-fpm:9000; }" > /etc/nginx/conf.d/upstream.conf
#
#EXPOSE 80
#EXPOSE 443
