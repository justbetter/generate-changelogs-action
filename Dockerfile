FROM php:8.3-alpine

RUN mkdir /app
COPY . /app

WORKDIR /app

COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer install

RUN ["chmod", "+x", "/app/entrypoint.sh"]

ENTRYPOINT ["/app/entrypoint.sh"]