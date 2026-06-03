FROM dunglas/frankenphp:1.4.2-php8.4-alpine AS base

WORKDIR /srv/app

RUN apk --update upgrade \
    && apk add --no-cache \
       autoconf automake make gcc g++ bash icu-dev libzip-dev \
       git libc-dev nodejs npm


ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions gd pdo_mysql zip intl opcache bcmath amqp apcu

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
ENV ALLOW_PORTS=80

EXPOSE 80

FROM base AS dev
RUN install-php-extensions xdebug
COPY etc/frankenphp/Caddyfile /etc/caddy/Caddyfile
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]

FROM base AS prod
ENV APP_ENV=prod
ENV APP_DEBUG=0
COPY etc/frankenphp/Caddyfile /etc/caddy/Caddyfile
COPY . .
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN set -eux; \
	composer install --no-cache --prefer-dist

RUN apk add --no-cache libcap \
 && setcap -r /usr/local/bin/frankenphp || true

ENV SERVER_NAME=":{$PORT}"
CMD ["/usr/local/bin/frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
